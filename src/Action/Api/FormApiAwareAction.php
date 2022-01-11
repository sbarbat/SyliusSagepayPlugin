<?php

declare(strict_types=1);

namespace Sbarbat\SyliusSagepayPlugin\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Sbarbat\SyliusSagepayPlugin\SagepayFormApi;

abstract class FormApiAwareAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use GatewayAwareTrait;

    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = SagepayFormApi::class;
    }
}
