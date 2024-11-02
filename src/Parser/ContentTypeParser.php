<?php

declare(strict_types=1);

namespace Myail\Parser;

use Myail\Decoder\CommentDecoder;

class ContentTypeParser
{
    public function __construct() {}

    public function parse(string $string): array
    {
        $content_type_parser = new CommentDecoder();
        $string = $content_type_parser->decode($string);
        $array = explode(";", $string);
        $return = [];

        if (isset($array[0])) {
            list($type, $subtype) = explode("/", $array[0], 2);
            $return = [
                'type' => $type,
                'subtype' => $subtype
            ];
        }
        if (isset($array[1])) {
            array_shift($array);
            foreach ($array as $item) {
                list($name, $value) = explode('=', $item, 2);
                $return[trim($name)] = trim($value, '" ');
            }
        }

        return $return;
    }
}
