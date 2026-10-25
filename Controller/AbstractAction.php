<?php
/**
 * zahls.ch Payment Gateway
 *
 * Copyright © 2026 zahls (https://www.zahls.ch)
 * See LICENSE.txt for license details.
 *
 * @copyright   2026 zahls
 * @author      zahls <support@zahls.ch>
 * @package     magento2
 * @subpackage  zahls_payment_gateway
 */
namespace Zahls\PaymentGateway\Controller;

use Magento\Store\Model\ScopeInterface;

abstract class AbstractAction extends \Magento\Framework\App\Action\Action
{
    const PAYMENT_GATEWAY_ID = 'zahls_gateway_id';
    const PAYMENT_SECURITY_HASH = 'zahls_security_hash';

    public $context;
    public $checkoutSession;
    public $orderFactory;
    public $checkoutHelper;
    public $configSettings;
    public $logger;
    public $zahlsFactory;
    public $orderSender;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Zahls\PaymentGateway\Helper\Checkout $checkoutHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $configSettings,
        \Magento\Framework\Logger\Monolog $logger,
        \Zahls\ZahlsFactory $zahlsFactory,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
    ) {
        parent::__construct($context);
        $this->context         = $context;
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory    = $orderFactory;
        $this->checkoutHelper  = $checkoutHelper;
        $this->configSettings  = $configSettings;
        $this->logger          = $logger;
        $this->zahlsFactory    = $zahlsFactory;
        $this->orderSender     = $orderSender;
    }

    public function getZahlsConfig($storeId = null)
    {
        return $this->configSettings->getValue(
            'payment/zahls_payment',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function executeCancelAction()
    {
        $this->context->getMessageManager()->addError(
            __('An error occurred while processing your payment. Please try again later.')
        );
        if ($this->checkoutHelper->cancelCurrentOrder('')) {
            $this->checkoutHelper->restoreQuote();
        }
        $this->redirectToCheckoutCart();
    }

    public function redirectToCheckoutCart()
    {
        $this->_redirect('checkout/cart');
    }

    public function getOrderDetailByOrderId($orderId)
    {
        $order = $this->orderFactory
            ->create()
            ->loadByIncrementId($orderId);
        if (!$order || !$order->getId()) {
            return null;
        }
        return $order;
    }

    public function getZahlsInstance($storeId = null)
    {
        $config = $this->getZahlsConfig($storeId);
        return $this->zahlsFactory->create([
            'instance'  => $config['instance_name'],
            'apiSecret' => $config['api_secret'],
        ]);
    }
}
