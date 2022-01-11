<?php

declare(strict_types=1);

namespace Sbarbat\SyliusSagepayPlugin\Lib;

class SagepayTransactionType
{
    public const PAYMENT = 'PAYMENT';

    public const DEFERRED = 'DEFERRED';

    public const AUTHORISE = 'AUTHORISE';

    public const CANCEL = 'CANCEL';

    public const VOID = 'VOID';

    public const REFUND = 'REFUND';

    public const REPEAT = 'REPEAT';

    public const REPEATDEFERRED = 'REPEATDEFERRED';

    public const ABORT = 'ABORT';

    public const RELEASE = 'RELEASE';

    public const COMPLETE = 'COMPLETE';
}
