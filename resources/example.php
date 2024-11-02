<?php

require_once __DIR__ . '/vendor/autoload.php';

use Myail\POP3;

ini_set('xdebug.var_display_max_children', -1);
ini_set('xdebug.var_display_max_data', -1);
ini_set('xdebug.var_display_max_depth', -1);

try {
    $pop3 = new POP3([
        'hostname' => 'pop.example.com',
        'port' => 110,
        'auth' => [
            'username' => 'username',
            'password' => 'password',
        ],
        'is_ssl' => false,
        'timeout' => 10,
        'stream_options' => [
            'ssl' => []
        ],
        'mail_save_directory' => __DIR__ . '/save/'
    ]);

    $message_number = 1;
    var_dump($pop3->getStatus());
    var_dump($pop3->getMailSize($message_number));
    var_dump($pop3->getMailUid($message_number));
    var_dump($pop3->getMailSizeList());
    var_dump($pop3->getMailUidList());

    $pop3->deleteMail($message_number);
    var_dump($pop3->getMailHeader($message_number));
    var_dump($pop3->getMailHeaderParsed($message_number));
    var_dump($pop3->getMailRaw($message_number));
    var_dump($pop3->getMailHeaderRaw($message_number));
    var_dump($pop3->getMailBodyRaw($message_number));
    $pop3->saveMailRaw($message_number);
    $pop3->saveMailBodyRaw($message_number);

    $pop3->quit();

} catch (\Exception $ex) {
    echo $ex->getMessage();
    var_dump($ex);
}
