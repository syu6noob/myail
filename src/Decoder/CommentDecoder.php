<?php

declare(strict_types=1);

namespace Myail\Decoder;

class CommentDecoder
{
    public function __construct() {}

    public function decode(string $string): string
    {
        return preg_replace('/(?<!\\\)\(.*?(?<!\\\)\)/', '', $string) ?? '[REMOVE_COMMENT ERROR]';
    }
}
