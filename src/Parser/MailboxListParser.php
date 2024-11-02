<?php

declare(strict_types=1);

namespace Myail\Parser;

class MailboxListParser
{
    public function __construct() {}

    public function parse(string $string): array
    {
        $return = [];
        $mailboxes = preg_split('/(?<!\\\),/', $string);
        foreach ($mailboxes as $mailbox) {
            $return[] = trim($mailbox);
        }
        return $return;
    }
}
