<?php

declare(strict_types=1);

namespace Myail\Decoder;

use Myail\Decoder\QuotedPrintableStringDecoder;;

class MimeStringDecoder
{
    public function __construct() {}

    public function decode(string $string): string
    {
        $quoted_printable_string_decoder = new QuotedPrintableStringDecoder();
        return preg_replace_callback(
            '/(=\?)(?<charset>.+)\?(?<encoding>[qQbB])\?(?<text>(.+))(\?=)/',
            function ($value) use ($quoted_printable_string_decoder) {
                if (isset($value["charset"]) && isset($value["encoding"]) && isset($value["text"])) {
                    $text = $value["text"];
                    if (strtolower($value["encoding"]) === 'q') {
                        $text = $quoted_printable_string_decoder->decode($text);
                    } else {
                        $text = base64_decode($text);
                    }
                    $text = mb_convert_encoding($text, "UTF-8", $value["charset"]);
                    return $text;
                } else {
                    return "[ENCODE FAILED]";
                }
            },
            $string
        );
    }
}
