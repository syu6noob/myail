<?php

use Myail\Parser\GroupParser;
use PHPUnit\Framework\TestCase;

final class GroupParserTest extends TestCase
{
    private $group_parser;

    protected function setUp()
    {
        $this->group_parser = new GroupParser();
    }

    public function test_group_parser_1()
    {
        $input = 'A Group:Ed Jones <c@a.test>,joe@where.test,John <jdoe@one.test>;';
        $ideal_output = [
            'group_name' => 'A Group',
            'mailbox_list' => 'Ed Jones <c@a.test>,joe@where.test,John <jdoe@one.test>',
        ];
        $this->assertSame(
            $ideal_output,
            $this->group_parser->parse($input)
        );
    }

    public function test_group_parser_2()
    {
        $input = "A Group(Some people) :Chris Jones <c@(Chris's host.)public.example>, joe@example.org, John <jdoe@one.test> (my dear friend); (the end of the group)";
        $ideal_output = [
            'group_name' => 'A Group(Some people)',
            'mailbox_list' => "Chris Jones <c@(Chris's host.)public.example>, joe@example.org, John <jdoe@one.test> (my dear friend)",
        ];
        $this->assertSame(
            $ideal_output,
            $this->group_parser->parse($input)
        );
    }

    public function test_group_parser_3()
    {
        $input = '(Empty list)(start)Hidden recipients  :(nobody(that I know))  ;';
        $ideal_output = [
            'group_name' => '(Empty list)(start)Hidden recipients',
            'mailbox_list' => '(nobody(that I know))'
        ];
        $this->assertSame(
            $ideal_output,
            $this->group_parser->parse($input)
        );
    }

    public function test_group_parser_4()
    {
        $input = 'Mary Smith <mary@example.net>';
        $ideal_output = [
            'mailbox_list' => 'Mary Smith <mary@example.net>'
        ];
        $this->assertSame(
            $ideal_output,
            $this->group_parser->parse($input)
        );
    }

    public function test_group_parser_5()
    {
        $input = 'Hidden recipients  :;';
        $ideal_output = [
            'group_name' => 'Hidden recipients',
            'mailbox_list' => ''
        ];
        $this->assertSame(
            $ideal_output,
            $this->group_parser->parse($input)
        );
    }
}
