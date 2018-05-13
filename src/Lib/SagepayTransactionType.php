<?php

declare(strict_types=1);

namespace Sbarbat\SyliusSagepayPlugin\Lib;

use Sbarbat\SyliusSagepayPlugin\Lib\SagepayException;

class SagepayTransactionType
{
    const PAYMENT = 'PAYMENT';
    const DEFERRED = 'DEFERRED';
    const AUTHORISE = 'AUTHORISE';
    const CANCEL = 'CANCEL';
    const VOID = 'VOID';
    const REFUND = 'REFUND';
    const REPEAT = 'REPEAT';
    const REPEATDEFERRED = 'REPEATDEFERRED';
    const ABORT = 'ABORT';
    const RELEASE = 'RELEASE';
    const COMPLETE = 'COMPLETE';
}
