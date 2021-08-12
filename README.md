## Overview

This plugins provides integration for Direct (server) Sagepay integrations. It supports 3D Secure.

This plugin is been used at least on one production website with over 10,000 successfully processed transactions.


![Sylius sagepay integration](https://raw.githubusercontent.com/sbarbat/SyliusSagepayPlugin/master/img/sylius-sagepay-integration.png)

## Installation

```bash
$ composer require sbarbat/sylius-sagepay-plugin
```
    
Add plugin dependencies to your AppKernel.php file:
```php
public function registerBundles()
{
    return array_merge(parent::registerBundles(), [
        ...
        
        new \Sbarbat\SyliusSagepayPlugin\SbarbatSyliusSagepayPlugin(),
    ]);
}
```

### Optional Installation steps

#### Money Amount Conversion

By default sylius stores prices as integer values representing the amount in cents/pence or smallest unit. 

If you have modified sylius to store money amounts in a different format, or with a different precision, then you will need to override.

Example, if your copy of sylius stores 4 decimals instead of 2, then you will need to override the class like this:

```php
<?php declare(strict_types = 1);

namespace App\Provider\Sagepay;

use Sbarbat\SyliusSagepayPlugin\Provider\AmountProvider as BaseAmountProvider;
use Sylius\Component\Core\Model\PaymentInterface;


class AmountProvider extends BaseAmountProvider
{
	public function getAmount(PaymentInterface $payment): string
	{
		return (string) ($payment->getAmount() / 10000);
	}
}
```

and add an entry to your service config to point to it:
```yaml
    Sbarbat\SyliusSagepayPlugin\Provider\AmountProvider:
        class: App\Provider\Sagepay\AmountProvider

```

### Test Cards

- https://www.sagepay.co.uk/support/12/36/test-card-details-for-your-test-transactions


### Sagepay Direct Integration Protocol and Guidelines

- http://integrations.sagepay.co.uk/content/getting-started-integrate-using-your-own-form


### Sagepay Form Integration Protocol and Guidelines

- https://www.sagepay.co.uk/file/25041/download-document/FORM_Integration_and_Protocol_Guidelines_270815.pdf

## Support

Do you want us to customize this plugin for your specific needs? Write us an email on barbatsan@gmail.com
