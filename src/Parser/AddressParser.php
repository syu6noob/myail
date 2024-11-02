<?php

declare(strict_types=1);

namespace Myail\Parser;

class AddressParser
{
    public function __construct() {}

    public function parseAddressList(string $string): array
    {
        $address_list_parser = new AddressListParser();
        $group_parser = new GroupParser();
        $mailbox_list_parser = new MailboxListParser();
        $mailbox_parser = new MailboxParser();

        $return = [];
        $address_list = $address_list_parser->parse($string);
        foreach ($address_list as $address) {
            $result = $group_parser->parse($address);
            $array = [
                'mailboxes' => []
            ];
            if (isset($result['group_name'])) {
                $array['group_name'] = $result['group_name'];
            }
            if (isset($result['mailbox_list'])) {
                $mailbox_list = $mailbox_list_parser->parse($result['mailbox_list']);
                foreach ($mailbox_list as $mailbox) {
                    $array['mailboxes'][] = $mailbox_parser->parse($mailbox);
                }
            }
            $return[] = $array;
        }
        return $return;
    }

    public function parseMailboxList(string $string): array
    {
        $mailbox_list_parser = new MailboxListParser();
        $mailbox_parser = new MailboxParser();

        $return = [];
        $mailbox_list = $mailbox_list_parser->parse($string);
        foreach ($mailbox_list as $mailbox) {
            $return[] = $mailbox_parser->parse($mailbox);
        }
        return $return;
    }
}
