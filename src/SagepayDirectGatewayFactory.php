<?php

declare(strict_types=1);

namespace Sbarbat\SyliusSagepayPlugin;

use Sbarbat\SyliusSagepayPlugin\Action\Integrations\Direct\AuthorizeAction;
use Sbarbat\SyliusSagepayPlugin\Action\Integrations\Direct\CancelAction;
use Sbarbat\SyliusSagepayPlugin\Action\Integrations\Direct\ConvertPaymentAction;
use Sbarbat\SyliusSagepayPlugin\Action\Integrations\Direct\CaptureAction;
use Sbarbat\SyliusSagepayPlugin\Action\Integrations\Direct\NotifyAction;
use Sbarbat\SyliusSagepayPlugin\Action\Integrations\Direct\RefundAction;
use Sbarbat\SyliusSagepayPlugin\Action\Integrations\Direct\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class SagepayDirectGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'sagepay_direct',
            'payum.factory_title' => 'Sagepay Direct',

            'payum.sagepay.template.layout' => '@PayumSagepay/card_details.html.twig',
            'payum.sagepay.merchant_session_route_name' => 'sbarbat_sylius_sagepay_plugin_merchant_session',

            'payum.action.capture' => new CaptureAction(),
            // No needed for form integration for now
            //'payum.action.notify' => new NotifyAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert' => new ConvertPaymentAction(),
        ]);

        if (null === $config['payum.api'] || false === $config['payum.api']) {
            $config['payum.default_options'] = array(
                'integration' => SagepayIntegrations::DIRECT,
                'sandbox' => true,
                'vendorName' => 'winebuyers',
                'integrationKeyLive' => '',
                'integrationPasswordLive' => '',
                'integrationKeyTest' => '',
                'integrationPasswordTest' => '',
                'stateCodeAbbreviated' => false,
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = [
                'vendorName',
                'integrationKeyLive',
                'integrationPasswordLive',
                'integrationKeyTest',
                'integrationPasswordTest'
            ];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new SagepayDirectApi((array) $config, $config['payum.http_client'], $config['httplug.message_factory']);
            };
        }

        $config['payum.paths'] = array_replace([
            'PayumSagepay' => __DIR__.'/Resources/views',
        ], $config['payum.paths'] ?: []);
    }
}
