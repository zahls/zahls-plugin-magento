# zahls.ch Payment Gateway for Magento 2

Accept TWINT, cards, PostFinance and more in Magento 2 via [zahls.ch](https://www.zahls.ch).

## Requirements

- Magento 2 / Adobe Commerce
- PHP 8.0+
- zahls.ch account

## Install

1. Download [`Zahls_PaymentGateway.zip`](https://github.com/zahls/zahls-plugin-magento/releases/latest/download/Zahls_PaymentGateway.zip) from the [latest release](https://github.com/zahls/zahls-plugin-magento/releases/latest).
2. Extract into `app/code/` so you get `app/code/Zahls/PaymentGateway/`.
3. Run:

```bash
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:clean
```

4. Configure under **Stores → Configuration → Sales → Payment Methods**:
   - **Platform:** `zahls.ch`
   - **Instance Name** and **API Secret** from zahls.ch → API & Integrations
5. Set the webhook URL in your zahls.ch dashboard (`https://your-shop.example/zahls/payment/webhook`).

## License

OSL-3.0 (see `LICENSE.txt`)

