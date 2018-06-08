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

class SagepayIntegrations
{    
    const FORM = 'FORM';
    const DIRECT = 'DIRECT';
    const SERVER = 'SERVER';
}
