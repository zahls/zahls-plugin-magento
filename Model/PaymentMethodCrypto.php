<?php
/**
 * zahls.ch Payment Gateway
 *
 * @author      zahls <support@zahls.ch>
 * @copyright   2026 zahls
 * @package     magento2
 * @subpackage  zahls_payment_gateway
 */
namespace Zahls\PaymentGateway\Model;

/**
 * PaymentMethod model for zahls.ch
 *
 */
class PaymentMethodCrypto extends ZahlsBase
{
    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = 'zahls_payment_crypto';
}
