<?php
/**
 * zahls.ch Payment Gateway
 *
 * Copyright © 2024 zahls (https://www.zahls.ch)
 * See LICENSE.txt for license details.
 *
 * @copyright   2026 zahls
 * @author      zahls <support@zahls.ch>
 * @package     magento2
 * @subpackage  zahls_payment_gateway
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
class PaymentMethodPayByBank extends ZahlsBase
{
    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = 'zahls_payment_pay_by_bank';
}
