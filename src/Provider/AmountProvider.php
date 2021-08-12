<?php

declare(strict_types=1);

namespace Sbarbat\SyliusSagepayPlugin\Provider;

use Sylius\Component\Core\Model\PaymentInterface;

class AmountProvider
{
    public function getAmount(PaymentInterface $payment): string
    {
        return (string) ($payment->getAmount() / 100);
    }
}
