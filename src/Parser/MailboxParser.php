<?php

declare(strict_types=1);

namespace Myail\Parser;

use Myail\Decoder\HeaderDecoder;

class MailboxParser
{
    public function __construct() {}

    public function parse(string $string): array
    {
        $header_decoder = new HeaderDecoder();

        $pattern = '/(?<!\\\)<(.+)(?<!\\\)>/';
        $result = preg_match($pattern, $string, $matches);
        $string_deleted = preg_replace($pattern, '', $string);

        if ($result) {
            return [
                "display_name" => $header_decoder->decode(trim($string_deleted), false),
                "address" => trim($matches[1])
            ];
        } else {
            return [
                "address" => trim($string_deleted)
            ];
        }
    }
}
