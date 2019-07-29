<?php

namespace Sbarbat\SyliusSagepayPlugin\Sanitizers;

abstract class AbstractSanitizer implements SanitizerInterface
{
    protected $maxLength = 20;

    public function sanitize(string $str): string
    {
        return $this->ensureMaxLength($this->clean($str), $this->maxLength);
    }

    protected function ensureMaxLength(string $str, int $maxLength): string
    {
        $length = strlen($str);

        return substr($str, 0, $maxLength - 1);
    }
    
    protected function clean(string $str): string
    {         
        return preg_replace('/[^A-Za-z0-9\-\ ]/', '', $str);
    }
}