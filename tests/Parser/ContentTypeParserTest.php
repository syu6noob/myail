<?php

namespace Parser;

use Myail\Parser\ContentTypeParser;
use PHPUnit\Framework\TestCase;

final class ContentTypeParserTest extends TestCase
{
    private $content_type_parser;

    protected function setUp()
    {
        $this->content_type_parser = new ContentTypeParser();
    }

    public function test_content_type_parser_1()
    {
        $input = 'multipart/mixed; boundary="boundary_str"';
        $ideal_output = [
            'type' => 'multipart',
            'subtype' => 'mixed',
            'boundary' => 'boundary_str'
        ];
        $this->assertSame(
            $ideal_output,
            $this->content_type_parser->parse($input)
        );
    }

    public function test_content_type_parser_2()
    {
        $input = 'text/plain; charset=us-ascii (Plain text)';
        $ideal_output = [
            'type' => 'text',
            'subtype' => 'plain',
            'charset' => 'us-ascii'
        ];
        $this->assertSame(
            $ideal_output,
            $this->content_type_parser->parse($input)
        );
    }
}
