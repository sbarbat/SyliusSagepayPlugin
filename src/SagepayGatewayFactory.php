<?php

declare(strict_types=1);

namespace Sbarbat\SyliusSagepayPlugin;

use Sbarbat\SyliusSagepayPlugin\Action\AuthorizeAction;
use Sbarbat\SyliusSagepayPlugin\Action\CancelAction;
use Sbarbat\SyliusSagepayPlugin\Action\ConvertPaymentAction;
use Sbarbat\SyliusSagepayPlugin\Action\CaptureAction;
use Sbarbat\SyliusSagepayPlugin\Action\NotifyAction;
use Sbarbat\SyliusSagepayPlugin\Action\RefundAction;
use Sbarbat\SyliusSagepayPlugin\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class SagepayGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'sagepay',
            'payum.factory_title' => 'Sagepay',
            'payum.action.capture' => new CaptureAction(),
            // No needed for form integration for now
            //'payum.action.notify' => new NotifyAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert' => new ConvertPaymentAction(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'integration' => 'form',
                'sandbox' => true,
                'currency' => 'GBP',
                'vendorName' => '',
                'protocolVersion' => '3.00',
                'encryptionPasswordLive' => '',
                'encryptionPasswordTest' => '',
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = [ 'vendorName', 'encryptionPasswordLive', 'encryptionPasswordTest', 'currency' ];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new SagepayFormApi((array) $config, $config['payum.http_client'], $config['httplug.message_factory']);
            };
        }
    }
}
