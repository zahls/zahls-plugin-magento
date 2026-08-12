<?php
/**
 * zahls.ch Payment Gateway
 *
 * Copyright © 2022 zahls (https://www.zahls.ch)
 * See LICENSE.txt for license details.
 *
 * @copyright   2026 zahls
 * @author      zahls <support@zahls.ch>
 * @package     magento2
 * @subpackage  zahls_payment_gateway
 * @version     1.0.1
 */
namespace Zahls\PaymentGateway\Observer;

use Exception;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class RestoreCartObserver implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @codeCoverageIgnore
     */
    public function __construct(\Magento\Checkout\Model\Session $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @codeCoverageIgnore
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $order = $this->checkoutSession->getLastRealOrder();
            if (!$order || !$order->getPayment()) {
                return;
            }

            $paymentMethod = $order->getPayment()->getMethod();
            $state = $order->getState();
            if (stripos($paymentMethod, 'zahls') === false) {
                return;
            }

            // restore cart items.
            if (in_array($state, [Order::STATE_NEW, Order::STATE_PENDING_PAYMENT])) {
                $order->registerCancellation('Order cancelled by customer')->save();

                $this->checkoutSession->restoreQuote();
            }

        } catch (Exception $e) {
            // Nothing to do.
        }
        return;
    }
}
