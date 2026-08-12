<?php
/**
 * zahls.ch Payment Gateway
 *
 * @author      zahls <support@zahls.ch>
 * @copyright   2026 zahls
 * @package     magento2
 * @subpackage  zahls_payment_gateway
 */

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Zahls_PaymentGateway',
    __DIR__
);
