<?php

declare(strict_types=1);

namespace Myail\Parser;

class AddressListParser
{
    public function __construct() {}

    public function parse(string $string): array
    {
        $return = [];
        $addresses = preg_split('/,(?=(?:[^:]*:|[^;]*$)(?:(?!;)[^;]*$))/', $string);
        foreach ($addresses as $address) {
            $return[] = trim($address);
        }
        return $return;
    }
}
