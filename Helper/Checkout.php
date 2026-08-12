<?php
/**
 * zahls.ch Payment Gateway
 *
 * Copyright © 2018 zahls (https://www.zahls.ch)
 * See LICENSE.txt for license details.
 *
 * @copyright   2026 zahls
 * @author      zahls <support@zahls.ch>
 * @package     magento2
 * @subpackage  zahls_payment_gateway
 * @version     1.0.0
 */
namespace Zahls\PaymentGateway\Helper;

/**
 * The Checkout helper help to give the current selected cart item detail
 */
class Checkout
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    public $checkoutSession;

    /**
     * Constructor
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Cancel last order
     *
     * @param  string  $comment Comment text
     * @return boolean True if order canceled successfully, false otherwise
     */
    public function cancelCurrentOrder($comment)
    {
        $order = $this->checkoutSession->getLastRealOrder();
        if ($order->getId() &&
            $order->getState() !== \Magento\Sales\Model\Order::STATE_CANCELED
        ) {
            $order->registerCancellation($comment)->save();
            return true;
        }
        return false;
    }

    /**
     * Restore last active quote
     *
     * @return bool True if quote restored successfully, false otherwise
     */
    public function restoreQuote()
    {
        return $this->checkoutSession->restoreQuote();
    }
}
