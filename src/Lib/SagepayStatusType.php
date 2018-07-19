<?php

declare(strict_types=1);

namespace Sbarbat\SyliusSagepayPlugin\Lib;

use Sbarbat\SyliusSagepayPlugin\Lib\SagepayException;

class SagepayStatusType
{
    // Transaction ok
    const OK = 'OK';

    // If the authorisation was failed by the bank.
    const NOTAUTHED = 'NOTAUTHED';

    // If the user decided to click cancel whilst on the Sage Pay payment pages.
    const ABORT = 'ABORT';

    // If authorisation occurred but your fraud screening rules were not met, or 3DAuthetnication failed three times.
    const REJECTED = 'REJECTED';

    // If an error has occurred at Sage Pay (these are very infrequent, but your site should handle them anyway. 
    // They normally indicate a problem with authorisation).
    const ERROR = 'ERROR';

    // Missing properties or badly formed body
    const MALFORMED = 'MALFORMED';

    //Invalid property values supplied
    const INVALID = 'INVALID';

    //Needs 3D authentication
    const _3DAUTH = '3DAUTH';
}