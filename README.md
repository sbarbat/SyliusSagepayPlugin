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

### For now this plugin only offer integration for Sagepay Form gateway.

### Sagepay Form Integration Protocol and Guidelines

- https://www.sagepay.co.uk/file/25041/download-document/FORM_Integration_and_Protocol_Guidelines_270815.pdf

## Support

Do you want us to customize this plugin for your specific needs? Write us an email on barbatsan@gmail.com :computer: