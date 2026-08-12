/**
 * zahls.ch Payment Gateway
 *
 * Copyright © 2023 zahls (https://www.zahls.ch)
 * See LICENSE.txt for license details.
 *
 * @copyright   2026 zahls
 * @author      zahls <support@zahls.ch>
 * @package     magento2
 * @subpackage  zahls_payment_gateway
 * @version     1.0.0
 */

/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/url',
        'googlePayLibraryJs',
    ],
    function (
        $,
        ko,
        Component,
        placeOrderAction,
        additionalValidators,
        url,
        googlePayJs
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Zahls_PaymentGateway/payment/payment-method',
            },

            /**
             * Get payment method data
             *
             * @returns {object}
             */
            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {}
                };
            },
            /**
             * Place order
             *
             * @param {object} data  jQuery Ui class
             * @param {object} event jQuery event
             * @returns {Boolean}
             */
            placeOrder: function (data, event) {
                var self = this;

                if (event) {
                    event.preventDefault();
                }

                if (this.validate() && additionalValidators.validate()) {
                    this.isPlaceOrderActionAllowed(false);

                    $.when(
                        placeOrderAction(this.getData(), this.messageContainer)
                    )
                        .fail(
                            function () {
                                self.isPlaceOrderActionAllowed(true);
                            }
                        ).done(
                            function () {
                                self.afterPlaceOrder();
                            }
                        );

                    return true;
                }
                return false;
            },

            /**
             * After place order callback
             */
            afterPlaceOrder: function () {
                // Redirect into controller
                $.mage.redirect(
                    url.build('zahls/payment/redirect')
                );
            },

            /**
             * Check the device support google pay or not.
             *
             * @returns bool
             */
            deviceSupported: function() {
                try {
                    const baseRequest = {
                        apiVersion: 2,
                        apiVersionMinor: 0
                    };
                    const allowedCardNetworks = ['MASTERCARD', 'VISA'];
                    const allowedCardAuthMethods = ['CRYPTOGRAM_3DS'];
                    const baseCardPaymentMethod = {
                        type: 'CARD',
                        parameters: {
                            allowedAuthMethods: allowedCardAuthMethods,
                            allowedCardNetworks: allowedCardNetworks
                        }
                    };

                    const isReadyToPayRequest = Object.assign({}, baseRequest);
                    isReadyToPayRequest.allowedPaymentMethods = [
                        baseCardPaymentMethod
                    ];
                    const paymentsClient = new google.payments.api.PaymentsClient(
                        {
                            environment: 'TEST'
                        }
                    );
                    paymentsClient.isReadyToPay(isReadyToPayRequest).then(function(response) {
                        if (response.result) {
                            console.log("zahls.ch Google Pay supported on this device/browser");
                            jQuery("#zahls_payment_google_pay").parent().parent('.payment-method').show();
                        } else {
                            console.warn("zahls.ch Google Pay is not supported on this device/browser");
                        }
                    }).catch(function(err) {
                        return false;
                    });
                } catch (err) {
                    return false;
                }
            },
        });
    }
);
