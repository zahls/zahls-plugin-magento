<?php

/**
 * zahls.ch Magento2 Module using \Magento\Payment\Model\Method\AbstractMethod
 * Copyright © 2026 zahls
 *
 * This file is part of Zahls/PaymentGateway.
 */

namespace Zahls\PaymentGateway\Plugin;

/**
 * Description of CsrfValidatorSkip
 *
 * @author      zahls <support@zahls.ch>
 */
class CsrfValidatorSkip {
    /**
     * @param \Magento\Framework\App\Request\CsrfValidator $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\ActionInterface $action
     */
    public function aroundValidate(
        $subject,
        \Closure $proceed,
        $request,
        $action
    ) {
        if ("{$request->getModuleName()}/{$request->getActionName()}" == 'zahls/webhook') {
            return; // Skip CSRF check
        }
        $proceed($request, $action); // Proceed Magento 2 core functionalities
    }

}
