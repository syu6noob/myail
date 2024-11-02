<?php

namespace Parser;

use Myail\Parser\MailboxParser;
use PHPUnit\Framework\TestCase;

final class MailboxParserTest extends TestCase
{
    private $mailbox_list_parser;

    protected function setUp()
    {
        $this->mailbox_parser = new MailboxParser();
    }

    public function test_mailbox_parser_1()
    {
        $input = 'Pete <pete@silly.example>';
        $ideal_output = [
            "display_name" => 'Pete',
            "address" => 'pete@silly.example'
        ];
        $this->assertSame(
            $ideal_output,
            $this->mailbox_parser->parse($input)
        );
    }

    public function test_mailbox_parser_2()
    {
        $input = '"Joe Q. Public" <john.q.public@example.com>';
        $ideal_output = [
            "display_name" => 'Joe Q. Public',
            "address" => 'john.q.public@example.com'
        ];
        $this->assertSame(
            $ideal_output,
            $this->mailbox_parser->parse($input)
        );
    }

    public function test_mailbox_parser_3()
    {
        $input = 'jdoe@example.org';
        $ideal_output = [
            "address" => 'jdoe@example.org'
        ];
        $this->assertSame(
            $ideal_output,
            $this->mailbox_parser->parse($input)
        );
    }
}
