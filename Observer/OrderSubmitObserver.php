<?php
/**
 * zahls.ch Payment Gateway
 *
 * Copyright © 2023 zahls (https://www.zahls.ch)
 * See LICENSE.txt for license details.
 *
 * @copyright   2026 zahls
 * @author      zahls <support@zahls.ch>
 * @package     magento2
 * @subpackage  zahls_payment_gateway
 */
namespace Zahls\PaymentGateway\Observer;
 
class OrderSubmitObserver
{
    /**
     * Before execute the order submit
     *
     * @param \Magento\Framework\App\Action\Action $subject
     * @param \Magento\Framework\Event\Observer $observer
     * @return array
     */
    public function beforeExecute($subject, $observer)
    {
        $order = $observer->getEvent()->getOrder();

        $paymentMethod = $order->getPayment()->getMethod();
        if (stripos($paymentMethod, 'zahls') === false) {
            return [$observer];
        }
        $order->setCanSendNewEmailFlag(false);
        return [$observer];
    }
}
