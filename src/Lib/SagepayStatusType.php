<?php

declare(strict_types=1);

namespace Sbarbat\SyliusSagepayPlugin\Lib;

class SagepayStatusType
{
    // Transaction ok
    public const OK = 'OK';

    // If the authorisation was failed by the bank.
    public const NOTAUTHED = 'NOTAUTHED';

    // If the user decided to click cancel whilst on the Sage Pay payment pages.
    public const ABORT = 'ABORT';

    // If authorisation occurred but your fraud screening rules were not met, or 3DAuthetnication failed three times.
    public const REJECTED = 'REJECTED';

    // If an error has occurred at Sage Pay (these are very infrequent, but your site should handle them anyway.
    // They normally indicate a problem with authorisation).
    public const ERROR = 'ERROR';

    // Missing properties or badly formed body
    public const MALFORMED = 'MALFORMED';

    //Invalid property values supplied
    public const INVALID = 'INVALID';

    //Needs 3D authentication
    public const _3DAUTH = '3DAUTH';
}
