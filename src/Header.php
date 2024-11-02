<?php

declare(strict_types=1);

namespace Myail;

use Myail\Parser\AddressParser;
use Myail\Parser\ContentTypeParser;
use Myail\Decoder\HeaderDecoder;

class Header
{
    public $header_array = [];
    public $header_parsed_array = [];
    public $header_raw_array = [];

    private $address_parser;
    private $content_type_parser;
    private $header_decoder;

    public function __construct() {
        $this->address_parser = new AddressParser();
        $this->content_type_parser = new ContentTypeParser();
        $this->header_decoder = new HeaderDecoder();
    }

    public function inputLine($line) {
        if ($line === "") $this->parse($this->header_raw_array[count($this->header_raw_array) - 1]);
        else if ($line === ltrim($line)) {
            if (count($this->header_raw_array) !== 0) {
                $this->parse($this->header_raw_array[count($this->header_raw_array) - 1]);
            }
            $this->header_raw_array[] = $line;
        } else {
            $this->header_raw_array[count($this->header_raw_array) - 1] .= "\n";
            $this->header_raw_array[count($this->header_raw_array) - 1] .= ltrim($line);
        }
//        var_dump($this->header_raw_array);
//        flush();
    }

    public function parse(string $field)
    {
        list($name, $value) = explode(':', $field, 2);
        $name = strtolower(trim($name));
        $value = trim($value);

        $this->header_array[$name] = $value;

        switch ($name) {
            case 'to':
                // no break
            case 'cc':
                // no break;
            case 'bcc':
                // no break;
            case 'reply-to':
                $address = $this->address_parser->parseAddressList($value);
                $this->header_parsed_array[$name] = $address;
                break;

            case 'from':
                // no break;
            case 'sender':
                $mailbox = $this->address_parser->parseMailboxList($value);
                $this->header_parsed_array[$name] = $mailbox;
                break;

            case 'subject':
                $this->header_parsed_array[$name] = $this->header_decoder->decode(
                    $value, true, false
                );
                break;

            case 'date':
                // no break;
            case 'message-id':
                // no break;
            case 'references':
                // no break;
            case 'in-reply-to':
                // no break;
            case 'mime-version':
                // no break;
            case 'content-transfer-encoding':
                $this->header_parsed_array[$name] = $value;
                break;

            case 'content-type':
                $this->header_parsed_array[$name] = $this->content_type_parser->parse($value);
                break;
        }
    }

    public function get()
    {
        $result = $this->header_array;
        $this->header_array = [];
        return $result;
    }

    public function getParsed()
    {
        $result = $this->header_parsed_array;
        $this->header_parsed_array = [];
        return $result;
    }
}
