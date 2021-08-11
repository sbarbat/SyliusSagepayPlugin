<?php

declare(strict_types=1);

namespace Sbarbat\SyliusSagepayPlugin\Sanitizers;

interface SanitizerInterface
{
    public function sanitize(string $str): string;
}
