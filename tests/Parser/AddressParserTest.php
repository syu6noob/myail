<?php

namespace Parser;

use Myail\Parser\AddressParser;
use PHPUnit\Framework\TestCase;

final class AddressParserTest extends TestCase
{
    private $address_parser;

    protected function setUp()
    {
        $this->address_parser = new AddressParser();
    }

    //-----------------------------------------------------
    // parseAddressList()
    //-----------------------------------------------------

    public function test_address_parser_01()
    {
        $input = 'John Doe <jdoe@machine.example>';
        $ideal_output = [
            [
                'mailboxes' => [
                    [
                        'display_name' => 'John Doe',
                        'address' => 'jdoe@machine.example'
                    ]
                ]
            ]
        ];
        $this->assertSame(
            $ideal_output,
            $this->address_parser->parseAddressList($input)
        );
    }

    public function test_address_parser_02()
    {
        $input = 'Mary Smith <mary@x.test>, jdoe@example.org, Who? <one@y.test>';
        $ideal_output = [
            [
                'mailboxes' => [
                    [
                        'display_name' => 'Mary Smith',
                        'address' => 'mary@x.test'
                    ]
                ]
            ],
            [
                'mailboxes' => [
                    [
                        'address' => 'jdoe@example.org'
                    ]
                ]
            ],
            [
                'mailboxes' => [
                    [
                        'display_name' => 'Who?',
                        'address' => 'one@y.test'
                    ]
                ]
            ]
        ];
        $this->assertSame(
            $ideal_output,
            $this->address_parser->parseAddressList($input)
        );
    }

    public function test_address_parser_03()
    {
        $input = 'A Group:Ed Jones <c@a.test>,joe@where.test,John <jdoe@one.test>;,Mary Smith <mary@example.net>';
        $ideal_output = [
            [
                'mailboxes' => [
                    [
                        'display_name' => 'Ed Jones',
                        'address' => 'c@a.test'
                    ],
                    [
                        'address' => 'joe@where.test'
                    ],
                    [
                        'display_name' => 'John',
                        'address' => 'jdoe@one.test'
                    ],
                ],
                'group_name' => 'A Group'
            ],
            [
                'mailboxes' => [
                    [
                        'display_name' => 'Mary Smith',
                        'address' => 'mary@example.net'
                    ]
                ]
            ]
        ];
        $this->assertSame(
            $ideal_output,
            $this->address_parser->parseAddressList($input)
        );
    }

    public function test_address_parser_04()
    {
        $input = "A Group(Some people):Chris Jones <c@(Chris's host.)public.example>,joe@example.org,John <jdoe@one.test> (my dear friend); (the end of the group)'";
        $ideal_output = [
            [
                'mailboxes' => [
                    [
                        'display_name' => 'Chris Jones',
                        'address' => "c@(Chris's host.)public.example"
                    ],
                    [
                        'address' => 'joe@example.org'
                    ],
                    [
                        'display_name' => 'John  (my dear friend)',
                        'address' => 'jdoe@one.test'
                    ],
                ],
                'group_name' => 'A Group(Some people)'
            ]
        ];
        $this->assertSame(
            $ideal_output,
            $this->address_parser->parseAddressList($input)
        );
    }

    public function test_address_parser_05()
    {
        $input = "=?ISO-2022-JP?Q?=22=1B=24B=24*L=3EA0=1B=28B=2Ecom=22?= <admin@onamae.com>";
        $ideal_output = [
            [
                'mailboxes' => [
                    [
                        'display_name' => 'お名前.com',
                        'address' => "admin@onamae.com"
                    ]
                ]
            ]
        ];
        $this->assertSame(
            $ideal_output,
            $this->address_parser->parseAddressList($input)
        );
    }


    //-----------------------------------------------------
    // parseAddressList()
    //-----------------------------------------------------

    public function test_address_parser_11()
    {
        $input = 'John Doe <jdoe@machine.example>';
        $ideal_output = [
            [
                'display_name' => 'John Doe',
                'address' => 'jdoe@machine.example'
            ]
        ];
        $this->assertSame(
            $ideal_output,
            $this->address_parser->parseMailboxList($input)
        );
    }

    public function test_address_parser_12()
    {
        $input = 'Mary Smith <mary@x.test>, jdoe@example.org, Who? <one@y.test>';
        $ideal_output = [
            [
                'display_name' => 'Mary Smith',
                'address' => 'mary@x.test'
            ],
            [
                'address' => 'jdoe@example.org'
            ],
            [
                'display_name' => 'Who?',
                'address' => 'one@y.test'
            ]
        ];
        $this->assertSame(
            $ideal_output,
            $this->address_parser->parseMailboxList($input)
        );
    }

    public function test_address_parser_13()
    {
        $input = '"Joe Q. Public" <john.q.public@example.com>';
        $ideal_output = [
            [
                'display_name' => 'Joe Q. Public',
                'address' => 'john.q.public@example.com'
            ]
        ];
        $this->assertSame(
            $ideal_output,
            $this->address_parser->parseMailboxList($input)
        );
    }

    public function test_address_parser_14()
    {
        $input = "A Group(Some people):Chris Jones <c@(Chris's host.)public.example>,joe@example.org,John <jdoe@one.test> (my dear friend); (the end of the group)'";
        $ideal_output = [
            [
                'mailboxes' => [
                    [
                        'display_name' => 'Chris Jones',
                        'address' => "c@(Chris's host.)public.example"
                    ],
                    [
                        'address' => 'joe@example.org'
                    ],
                    [
                        'display_name' => 'John  (my dear friend)',
                        'address' => 'jdoe@one.test'
                    ],
                ],
                'group_name' => 'A Group(Some people)'
            ]
        ];
        $this->assertSame(
            $ideal_output,
            $this->address_parser->parseAddressList($input)
        );
    }
}
