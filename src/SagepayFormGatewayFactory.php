<?php

declare(strict_types=1);

namespace Sbarbat\SyliusSagepayPlugin;

use Sbarbat\SyliusSagepayPlugin\Action\Integrations\Form\ConvertPaymentAction;
use Sbarbat\SyliusSagepayPlugin\Action\Integrations\Form\CaptureAction;
use Sbarbat\SyliusSagepayPlugin\Action\Integrations\Form\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class SagepayFormGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'sagepay',
            'payum.factory_title' => 'Sagepay Form',
            'payum.action.capture' => new CaptureAction(),
            // No needed for form integration for now
            //'payum.action.notify' => new NotifyAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert' => new ConvertPaymentAction(),
        ]);

        if (!$config['payum.api']) {
            $config['payum.default_options'] = array(
                'integration' => SagepayIntegrations::FORM,
                'sandbox' => true,
                'vendorName' => '',
                'protocolVersion' => '3.00',
                'encryptionPasswordLive' => '',
                'encryptionPasswordTest' => '',
                'stateCodeAbbreviated' => false,
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = [ 'vendorName', 'encryptionPasswordLive', 'encryptionPasswordTest', 'currency' ];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new SagepayFormApi((array) $config, $config['payum.http_client'], $config['httplug.message_factory'], $config['sylius.repository.province']);
            };
        }
    }
}
