<?php
/**
 * zahls.ch Payment Gateway
 *
 * Copyright©2026 zahls (https://www.zahls.ch)
 * See LICENSE.txt for license details.
 *
 * @copyright   2026 zahls
 * @author      zahls <support@zahls.ch>
 * @package     magento2
 * @subpackage  zahls_payment_gateway
 * @version     1.0.0
 */
namespace Zahls\PaymentGateway\Controller\Payment;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\Order;
use Zahls\Models\Response\Gateway;
use Zahls\Models\Response\Transaction;

/**
 * Class \Zahls\PaymentGateway\Controller\Payment\Failure
 * The Failure controller is accessing from frontend
 */
class Failure extends \Zahls\PaymentGateway\Controller\AbstractAction
{
    /**
     * Execute payment failure.
     */
    public function execute()
    {
        $objectManager = ObjectManager::getInstance();
        $checkoutSession = $objectManager->create('\Magento\Checkout\Model\Session');
        $quoteFactory = $objectManager->create('\Magento\Quote\Model\QuoteFactory');

        $order = $checkoutSession->getLastRealOrder();

        if ($order && $order->getState() == Order::STATE_PENDING_PAYMENT) {
            $this->checkoutHelper->cancelCurrentOrder('Order cancelled by customer');
            $this->deleteZahlsGateway($order);
        }

        $quote = $quoteFactory->create()->loadByIdWithoutStore($order->getQuoteId());
        if ($quote->getId()) {
            $quote->setIsActive(1)->setReservedOrderId(null)->save();
            $checkoutSession->replaceQuote($quote);
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('checkout/cart');
            $this->messageManager->addWarningMessage('Your zahls.ch payment failed.');
            return $resultRedirect;
        }
        return $this->_redirect('checkout/onepage/failure');
    }

    /**
     * Delete the Gateway
     *
     * @param Order $order
     * @return void
     */
    private function deleteZahlsGateway($order): void
    {
        $payment = $order->getPayment();
        $gatewayId = $payment->getAdditionalInformation(
            static::PAYMENT_GATEWAY_ID
        );
        $zahls = $this->getZahlsInstance($order->getStoreId());
        $gateway = ObjectManager::getInstance()->create(
            '\Zahls\Models\Request\Gateway'
        );
        $gateway->setId($gatewayId);
        try {
            $zahlsGateway = $zahls->getOne($gateway);
        } catch (\Zahls\ZahlsException $e) {
            return;
        }
        if ($zahlsGateway) {
            $transaction = $this->getTransactionByGateway($zahlsGateway);
            if ($transaction == null) {
                try {
                    $zahls->delete($zahlsGateway);
                } catch (\Zahls\ZahlsException $e) {
                    // no action.
                }
            }
        }
    }

    public function getTransactionByGateway(Gateway $zahlsGateway): ?array
    {
        if (!in_array($zahlsGateway->getStatus(), [Transaction::CONFIRMED, Transaction::WAITING])) {
            return null;
        }
        $invoices = $zahlsGateway->getInvoices();
        if (!$invoices || !$invoice = end($invoices)) {
            return null;
        }

        if (!$transactions = $invoice['transactions']) {
            return null;
        }
        $zahlsTransaction = end($transactions);
        if (!in_array($zahlsTransaction['status'], [Transaction::CONFIRMED, Transaction::WAITING])) {
            return null;
        }
        return $zahlsTransaction;
    }
}
