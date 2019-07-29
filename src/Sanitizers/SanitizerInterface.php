<?php

namespace Sbarbat\SyliusSagepayPlugin\Sanitizers;

interface SanitizerInterface
{
    public function sanitize(string $str): string;
}