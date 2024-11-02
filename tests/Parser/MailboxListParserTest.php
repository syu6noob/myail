<?php

namespace Parser;

use Myail\Parser\MailboxListParser;
use PHPUnit\Framework\TestCase;

final class MailboxListParserTest extends TestCase
{
    private $mailbox_list_parser;

    protected function setUp()
    {
        $this->mailbox_list_parser = new MailboxListParser();
    }

    public function test_mailbox_list_parser_1()
    {
        $input = 'John Doe <jdoe@machine.example>';
        $ideal_output = [
            'John Doe <jdoe@machine.example>'
        ];
        $this->assertSame(
            $ideal_output,
            $this->mailbox_list_parser->parse($input)
        );
    }

    public function test_mailbox_list_parser_2()
    {
        $input = 'Mary Smith <mary@x.test>, jdoe@example.org, Who? <one@y.test>';
        $ideal_output = [
            'Mary Smith <mary@x.test>',
            'jdoe@example.org',
            'Who? <one@y.test>'
        ];
        $this->assertSame(
            $ideal_output,
            $this->mailbox_list_parser->parse($input)
        );
    }

    public function test_address_list_parser_3()
    {
        $input = '<boss@nil.test>, "Giant; \"Big\" Box" <sysservices@example.net>';
        $ideal_output = [
            '<boss@nil.test>',
            '"Giant; \"Big\" Box" <sysservices@example.net>',
        ];
        $this->assertSame(
            $ideal_output,
            $this->mailbox_list_parser->parse($input)
        );
    }

    public function test_address_list_parser_4()
    {
        $input = "joe@where.test,John <jdoe@one.test>";
        $ideal_output = [
            "joe@where.test",
            "John <jdoe@one.test>"
        ];
        $this->assertSame(
            $ideal_output,
            $this->mailbox_list_parser->parse($input)
        );
    }
}
