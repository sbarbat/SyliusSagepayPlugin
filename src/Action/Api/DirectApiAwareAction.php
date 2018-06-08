<?php

declare(strict_types=1);

namespace Sbarbat\SyliusSagepayPlugin\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;

use Sbarbat\SyliusSagepayPlugin\SagepayDirectApi;

abstract class DirectApiAwareAction implements GatewayAwareInterface, ApiAwareInterface
{
    use GatewayAwareTrait;
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = SagepayDirectApi::class;
    }
}
