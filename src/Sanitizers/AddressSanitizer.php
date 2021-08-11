<?php

declare(strict_types=1);

namespace Sbarbat\SyliusSagepayPlugin\Sanitizers;

class AddressSanitizer extends AbstractSanitizer implements SanitizerInterface
{
    protected $maxLength = 100;
}
