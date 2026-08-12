<?php

/**
 * zahls.ch Payment Gateway Module
 *
 * @category  zahls.ch
 * @package   Zahls_PaymentGateway
 * @author      zahls <support@zahls.ch>
 * @copyright   2026 zahls
 */

namespace Zahls\PaymentGateway\Model;

class PaymentMethodCembraPay extends ZahlsBase
{
    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = 'zahls_payment_cembrapay';
}
