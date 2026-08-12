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
 * @version     1.0.0
 */
namespace Zahls\PaymentGateway\Model;

/**
 * PaymentMethod model for zahls.ch
 *
 * @method \Magento\Quote\Api\Data\PaymentMethodExtensionInterface getExtensionAttributes()
 *
 * @api
 * @since 100.0.2
 */
class PaymentMethodOney extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * @var string
     */
    const PAYMENT_METHOD_ZAHLS_CODE = 'zahls_payment_oney';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_ZAHLS_CODE;

    /**
     * Run the payment initialize while order place
     *
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * @inheritdoc
     */
    public function initialize($paymentAction, $stateObject)
    {
        if ($paymentAction === static::ACTION_AUTHORIZE) {
            $stateObject->setState(
                \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT
            );
            $stateObject->setStatus(
                \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT
            );
            $stateObject->setIsNotified(false);
        }
    }
}
