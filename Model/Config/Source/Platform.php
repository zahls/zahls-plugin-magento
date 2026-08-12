<?php
/**
 * zahls.ch Payment Gateway
 *
 * @copyright   2026 zahls
 * @author      zahls <support@zahls.ch>
 * @package     magento2
 * @subpackage  zahls_payment_gateway
 */
namespace Zahls\PaymentGateway\Model\Config\Source;

/**
 * @codeCoverageIgnore
 * @api
 */
class Platform implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            'zahls.ch' => __('zahls.ch'),
        ];
    }
}
