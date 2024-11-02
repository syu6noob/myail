<?php

declare(strict_types=1);

namespace Myail\Decoder;

class QuotedPrintableStringDecoder
{
    public function __construct() {}

    public function decode(string $string): string
    {
        return quoted_printable_decode($string);
    }
}
