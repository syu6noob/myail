<?php

declare(strict_types=1);

namespace Myail\Decoder;

use Myail\Decoder\CommentDecoder;
use Myail\Decoder\MimeStringDecoder;
use Myail\Decoder\QuotedPrintableStringDecoder;

class HeaderDecoder
{
    public function __construct() {}

    public function decode(
        string $input,
        bool   $is_comment_decode = true,
        bool   $is_line_break_enabled = true
    ): string
    {
        $comment_decoder = new CommentDecoder();
        $mime_string_decoder = new MimeStringDecoder();
        $quoted_printable_string_decoder = new QuotedPrintableStringDecoder();

        $strings = explode("\n", $input);
        $return = "";
        foreach ($strings as $string) {
            $string = $is_comment_decode ? $comment_decoder->decode($string) : $string;
            $string = $mime_string_decoder->decode($string);
            $string = $quoted_printable_string_decoder->decode($string);
            $string = stripslashes($string);
            $string = trim($string, '"');

            if ($return !== "" && $is_line_break_enabled)  $return .= "\n";
            $return .= $string;
        }

        return $return;
    }
}
