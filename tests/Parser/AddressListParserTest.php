<?php

use Myail\Parser\AddressListParser;
use PHPUnit\Framework\TestCase;

final class AddressListParserTest extends TestCase
{
    private $address_list_parser;

    protected function setUp()
    {
        $this->address_list_parser = new AddressListParser();
    }

    public function test_address_list_parser_1()
    {
        $input = 'John Doe <jdoe@machine.example>';
        $ideal_output = [
            'John Doe <jdoe@machine.example>'
        ];
        $this->assertSame(
            $ideal_output,
            $this->address_list_parser->parse($input)
        );
    }

    public function test_address_list_parser_2()
    {
        $input = 'Mary Smith <mary@x.test>, jdoe@example.org, Who? <one@y.test>';
        $ideal_output = [
            'Mary Smith <mary@x.test>',
            'jdoe@example.org',
            'Who? <one@y.test>'
        ];
        $this->assertSame(
            $ideal_output,
            $this->address_list_parser->parse($input)
        );
    }

    public function test_address_list_parser_3()
    {
        $input = 'A Group:Ed Jones <c@a.test>,joe@where.test,John <jdoe@one.test>;,Mary Smith <mary@example.net>';
        $ideal_output = [
            'A Group:Ed Jones <c@a.test>,joe@where.test,John <jdoe@one.test>;',
            'Mary Smith <mary@example.net>'
        ];
        $this->assertSame(
            $ideal_output,
            $this->address_list_parser->parse($input)
        );
    }

    public function test_address_list_parser_4()
    {
        $input = "A Group(Some people):Chris Jones <c@(Chris's host.)public.example>,joe@example.org,John <jdoe@one.test> (my dear friend); (the end of the group)'";
        $ideal_output = [
            "A Group(Some people):Chris Jones <c@(Chris's host.)public.example>,joe@example.org,John <jdoe@one.test> (my dear friend); (the end of the group)'"
        ];
        $this->assertSame(
            $ideal_output,
            $this->address_list_parser->parse($input)
        );
    }
}
