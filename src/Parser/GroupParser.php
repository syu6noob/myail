<?php

declare(strict_types=1);

namespace Myail\Parser;

use Myail\Decoder\HeaderDecoder;

class GroupParser
{
    public function __construct() {}

    public function parse(string $string): array
    {
        $header_decoder = new HeaderDecoder();
        $result = strpos($string, ':');
        if ($result) {
            $result = preg_split('/(?<!\\\)[;:]/', $string, -1, PREG_SPLIT_NO_EMPTY);
            list($display_name, $mailbox_list) = $result;
            return [
                'group_name' => $header_decoder->decode(trim($display_name), false),
                'mailbox_list' => $mailbox_list ? trim($mailbox_list) : "",
            ];
        } else {
            return [
                'mailbox_list' => trim($string)
            ];
        }
    }
}
