<?php
/**
 * zahls.ch Payment Gateway
 *
 * @category  zahls.ch
 * @package   Zahls_PaymentGateway
 * @author      zahls <support@zahls.ch>
 * @copyright   2026 zahls
 */
namespace Zahls\PaymentGateway\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Renders a button with JavaScript to populate the API secret and instance name field.
 */
class ConnectPlatformButton extends Field
{
	/**
	 * @var string
	 */
	protected $_template = 'Zahls_PaymentGateway::system/config/connect_platform_button.phtml';

	/**
	 * @param Context $context
	 * @param array $data
	 */
	public function __construct(Context $context, array $data = [])
	{
		parent::__construct($context, $data);
	}

	/**
	 * Remove scope and label to render just the button.
	 *
	 * @param  AbstractElement $element
	 * @return string
	 */
	public function render(AbstractElement $element)
	{
		$element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
		return parent::render($element);
	}

	/**
	 * Return the block's HTML.
	 *
	 * @param  AbstractElement $element
	 * @return string
	 */
	protected function _getElementHtml(AbstractElement $element)
	{
		return $this->_toHtml();
	}
}
