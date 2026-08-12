<?php
/**
 * zahls.ch Payment Gateway
 *
 * @author      zahls <support@zahls.ch>
 * @copyright   2026 zahls
 * @package     magento2
 * @subpackage  zahls_payment_gateway
 */
namespace Zahls\PaymentGateway\Controller\Payment;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\Order;
use Zahls\Models\Response\Transaction;

/**
 * class \Zahls\PaymentGateway\Controller\Payment\Webhook
 * After completed the payment, This class to get the response which is sent
 * from zahls.ch payment call back.
 */
class Webhook extends \Zahls\PaymentGateway\Controller\AbstractAction
{

    const STATE_zahls_PARTIAL_REFUND = 'zahls_partial_refund';

    /**
     * Executes to receive post values from request.
     * The order status has been updated if the payment is successful
     */
    public function execute()
    {
        // Check payment getway response
        $post = $this->getRequest()->getPostValue();

        $requestTransaction = $post['transaction'] ?? null;
        $requestTransactionStatus = $requestTransaction['status'] ?? null;
        $orderId = $requestTransaction['invoice']['referenceId'] ?? null;

        if (!$requestTransaction || !$requestTransactionStatus || !$orderId) {
            throw new \Exception('zahls.ch Webhook Data incomplete');
        }

        $order = $this->getOrderDetailByOrderId($orderId);
        if (!$order) {
            throw new \Exception('No order found with ID ' . $orderId);
        }

        $payment   = $order->getPayment();
        $gatewayId = $payment->getAdditionalInformation(
            static::PAYMENT_GATEWAY_ID
        );
        $paymentHash = $payment->getAdditionalInformation(
            static::PAYMENT_SECURITY_HASH
        );
        if (!$this->isValidHash($requestTransaction, $paymentHash, $order->getStoreId())) {
            // Set the fraud status when payment is frauded.
            $order->setState(Order::STATUS_FRAUD);
            $order->setStatus(Order::STATUS_FRAUD);
            $order->save();
            throw new \Exception('Payment hash incorreect. Fraud suspect');
        }

        try {
            $zahls = $this->getZahlsInstance($order->getStoreId());
            $gateway = ObjectManager::getInstance()->create(
                '\Zahls\Models\Request\Gateway'
            );
            $gateway->setId($gatewayId);

            $zahlsGateway = $zahls->getOne($gateway);
            $invoices = $zahlsGateway->getInvoices();
            $invoice = end($invoices);

            $transactions = $invoice['transactions'];
            $transaction = end($transactions);

            $status = $transaction['status'];
        } catch (\Zahls\ZahlsException $e) {
            throw new \Exception('No zahls.ch Gateway found with ID: ' . $gatewayId);
        }

        if ($status !== $requestTransactionStatus) {
            throw new \Exception('Corrupt webhook status');
        }

        $state = '';
        switch ($status) {
            case Transaction::CONFIRMED:
                $state = Order::STATE_PROCESSING;
                break;
            case Transaction::CANCELLED:
            case Transaction::DECLINED:
            case Transaction::ERROR:
            case Transaction::EXPIRED:
                $state = Order::STATE_CANCELED;
                break;
            case Transaction::REFUNDED:
                $state = Order::STATE_CLOSED;
                break;
            case Transaction::WAITING:
                $state = Order::STATE_PENDING_PAYMENT;
                break;
            case Transaction::PARTIALLY_REFUNDED:
                try {
                    $state = self::STATE_zahls_PARTIAL_REFUND;
                    $orderStatusCollection = ObjectManager::getInstance()->create(
                        '\Magento\Sales\Model\ResourceModel\Order\Status\Collection'
                    );
                    $orderStatusCollection = $orderStatusCollection->toOptionArray();
                    $zahlsPartialRefund = array_search($state, array_column($orderStatusCollection, 'value'));
                    if (!$zahlsPartialRefund) { // if custom order status does not exit.
                        $state = Order::STATE_CLOSED;
                    }
                } catch (\Exception $e) {
                    $state = Order::STATE_CLOSED;
                }
                break;
        }
        if (empty($state)) {
            return;
        }
        if (!$this->isAllowedToChangeState($order->getState(), $state)) {
            return;
        }
        if ($state === Order::STATE_CANCELED && $order->canCancel()) {
            $order->registerCancellation('Order was canceled via zahls.ch webhook')->save();
        } else {
            $order->setState($state);
            $order->setStatus($state);
            $order->addCommentToStatusHistory('Order Status updated via zahls.ch Webhook');
            $order->save();
        }

        // Create Invoice
        $magentoInvoice = null;
        if ($state === Order::STATE_PROCESSING && $order->canInvoice()) {
            $invoiceService = ObjectManager::getInstance()->create(
                '\Magento\Sales\Model\Service\InvoiceService'
            );
            $transaction = ObjectManager::getInstance()->create(
                '\Magento\Framework\DB\Transaction'
            );
            $invoice = $invoiceService->prepareInvoice($order);
            $invoice->register();
            $invoice->pay();
            $invoice->save();

            $transactionSave = $transaction
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());
            $transactionSave->save();
            $magentoInvoice = $invoice;
        }

        // Send order confirmation mail
        if ($state === Order::STATE_PROCESSING && !$order->getEmailSent()) {
            $order->setCanSendNewEmailFlag(true);
            $order->save();
            $this->orderSender->send($order, true);
        }

        // Send invoice email
        if (
            $magentoInvoice
            && $state === Order::STATE_PROCESSING
            && !$magentoInvoice->getEmailSent()
        ) {
            $invoiceSender = ObjectManager::getInstance()->create(
                '\Magento\Sales\Model\Order\Email\Sender\InvoiceSender'
            );
            try {
                $invoiceSender->send($magentoInvoice);
                $magentoInvoice->setEmailSent(true);
                $magentoInvoice->save();
            } catch (\Exception $e) {
                echo $e->getMessage();
                exit;
            }
        }
    }

    /**
     * Check hash value is valid or not
     *
     * @param  array   $transaction Post Values
     * @param  string  $paymentHash Saved hash value
     * @param  int     $storeId
     * @return boolean True if the hash values is equal, false otherwise
     */
    private function isValidHash($transaction, $paymentHash, $storeId)
    {
        $postHash = $transaction['invoice']['paymentLink']['hash'];
        $config   = $this->getZahlsConfig($storeId);
        $hash     = hash_hmac('sha1', $postHash, $config['api_secret'], false);
        // Check hash value difference
        if (strcasecmp($hash, $paymentHash) === 0) {
            return true;
        }
        return false;
    }

    /**
     * Check the transition is allowed or not
     *
     * @param string $oldState
     * @param string $newState
     * @return bool
     */
    private function isAllowedToChangeState($oldState, $newState)
    {
        switch ($oldState) {
            case Order::STATE_PENDING_PAYMENT:
                return in_array($newState, [
                    Order::STATE_PROCESSING,
                    Order::STATE_CLOSED,
                    Order::STATE_CANCELED,
                ]);
            case Order::STATE_PROCESSING:
            case Order::STATE_COMPLETE:
                return in_array($newState, [
                    Order::STATE_CLOSED,
                    self::STATE_zahls_PARTIAL_REFUND,
                ]);
            case Order::STATE_CLOSED:
                return false;
            case Order::STATE_CANCELED:
                return in_array($newState, [
                    Order::STATE_PROCESSING,
                    Order::STATE_PENDING_PAYMENT
                ]);
            case self::STATE_zahls_PARTIAL_REFUND:
                return in_array($newState, [
                    Order::STATE_CLOSED,
                ]);
        }
        return false;
    }
}
