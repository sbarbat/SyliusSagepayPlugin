## Overview

This plugins provides integration for Direct and Form Sagepays integrations. It supports 3D Secure.

The Direct integration is been used at least on one production website with over 10,000 transactions.

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


### Test Cards

- https://www.sagepay.co.uk/support/12/36/test-card-details-for-your-test-transactions


### Sagepay Direct Integration Protocol and Guidelines

- http://integrations.sagepay.co.uk/content/getting-started-integrate-using-your-own-form


### Sagepay Form Integration Protocol and Guidelines

- https://www.sagepay.co.uk/file/25041/download-document/FORM_Integration_and_Protocol_Guidelines_270815.pdf

## Support

Do you want us to customize this plugin for your specific needs? Write us an email on barbatsan@gmail.com :computer:
