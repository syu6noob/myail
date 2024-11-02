<?php

namespace Myail;

use Myail\Client\POP3Client;
use Myail\Exception\FileManagementException;

use Myail\Exception\AuthorizationException;
use Myail\Exception\Client\ClientAuthorizationException;
use Myail\Exception\Client\ClientConnectException;
use Myail\Exception\Client\ClientException;
use Myail\Exception\Client\ClientServerResponseException;
use Myail\Exception\Client\ClientStatusException;
use Myail\Exception\Client\ClientTimeoutException;
use Myail\Parser\ContentTypeParser;

class POP3
{
    /** @var POP3Client $client */
    private $client = null;

    /** @var string $mail_save_directory */
    private $mail_save_directory = "";

    /**
     * Authorization
     *
     * @param array $auth_methods
     * @param string $auth_username
     * @param string $auth_password
     * @throws AuthorizationException
     * @throws ClientAuthorizationException
     * @throws ClientConnectException
     * @throws ClientException
     * @throws ClientServerResponseException
     * @throws ClientStatusException
     * @throws ClientTimeoutException
     */
    private function __auth(
        array $auth_methods,
        string $auth_username,
        string $auth_password
    )
    {
        foreach ($auth_methods as $method) {
            if (in_array($method, $this->client->getAuthMethodList())) {
                switch ($method) {
                    case 'user':
                        if ($this->client->auth($auth_username, $auth_password)) {
                            return true;
                        }
                        break;
                    case 'sasl_plain':
                        if ($this->client->authPlain($auth_username, $auth_password)) {
                            return true;
                        }
                        break;
                    case 'sasl_login':
                        if ($this->client->authLogin($auth_username, $auth_password)) {
                            return true;
                        }
                        break;
                }
            }
        }
        throw new AuthorizationException();
    }

    /**
     * @throws ClientServerResponseException
     * @throws ClientTimeoutException
     * @throws ClientAuthorizationException
     * @throws AuthorizationException
     * @throws ClientConnectException
     * @throws ClientStatusException
     * @throws ClientException
     */
    public function __construct(
        array $parameters
    )
    {
        $is_ssl         = $parameters['is_ssl'] ?? false;
        $hostname       = $parameters['hostname'] ?? '127.0.0.1';
        $port           = $parameters['port'] ?? (($is_ssl === true) ? 995 : 110);
        $timeout        = $parameters['timeout'] ?? 10;
        $stream_options = $parameters['stream_options'] ?? [];

        $auth_methods   = $parameters['auth']['methods'] ?? ['user', 'sasl_plain', 'sasl_login'];
        $auth_username  = $parameters['auth']['username'] ?? "";
        $auth_password  = $parameters['auth']['password'] ?? "";

        $this->mail_save_directory = $parameters['mail_save_directory'] ?? __DIR__;

        $this->client = new POP3Client(
            $hostname,
            $port,
            $is_ssl,
            $timeout,
            $stream_options
        );
        $this->client->open();
        $this->__auth($auth_methods, $auth_username, $auth_password);
    }

    public function quit()
    {
        $this->client->quit();
    }

    //-----------------------------------------------------
    // Manage Mails
    //-----------------------------------------------------

    /**
     * @throws ClientException
     */
    public function getStatus(): array
    {
        return $this->client->status();
    }

    /**
     * @param int $message_number
     * @return int
     * @throws ClientConnectException
     * @throws ClientException
     * @throws ClientServerResponseException
     */
    public function getMailSize(int $message_number): int
    {
        return intval($this->client->listMsg($message_number)[$message_number]);
    }

    /**
     * @param int $max_count
     * @return array
     * @throws ClientException
     */
    public function getMailSizeList(int $max_count = 50): array
    {
        $count = 0;
        $list = [];
        foreach ($this->client->list() as $message_num => $size)
        {
            if ($count >= $max_count && $max_count !== -1) break;
            $list[$message_num] = $size;
            $count++;
        }
//        var_dump($list);
        return $list;
    }

    /**
     * @param int $message_number
     * @return string
     * @throws ClientConnectException
     * @throws ClientException
     * @throws ClientServerResponseException
     */
    public function getMailUid(int $message_number): string
    {
        return $this->client->uidlMsg($message_number)[$message_number];
    }

    /**
     * @param int $max_count
     * @return array
     * @throws ClientException
     */
    public function getMailUidList(int $max_count = 50): array
    {
        $count = 0;
        $list = [];
        foreach ($this->client->uidl() as $message_num => $uidl)
        {
            if ($count >= $max_count && $max_count !== -1) break;
            $list[$message_num] = $uidl;
            $count++;
        }
//        var_dump($list);
        return $list;
    }

    /**
     * @param int $message_number
     * @return void
     * @throws ClientException
     * @throws ClientServerResponseException
     * @throws ClientStatusException
     * @throws ClientTimeoutException
     */
    public function deleteMail(int $message_number)
    {
        $this->client->delete($message_number);
    }

    //-----------------------------------------------------
    // Fetch a mail
    //-----------------------------------------------------

    /**
     * Make the directory which saves mails
     *
     * @param string $path
     * @throws FileManagementException
     */
    private function __makeSaveDirectory(string $path)
    {
        if (!file_exists($path)) {
            if (!mkdir($path, 0777, true)) {
                throw new FileManagementException('Failed to create a directory: ' . $path);
            }
        }
    }

    /**
     * @param int $message_number
     * @return string
     * @throws ClientException
     * @throws ClientStatusException
     * @throws ClientTimeoutException
     */
    public function getMailHeaderRaw(int $message_number): string
    {
        $header_raw = "";
        $this->client->top($message_number, 0);
        while (true) {
            $line = $this->client->receiveOneLine();
            if ($line === true) break;
            else if ($line === "") continue;
            else {
                $header_raw .= "\n";
                $header_raw .= $line;
            }
        }
        return $header_raw;
    }

    /**
     * @param int $message_number
     * @return array|mixed
     * @throws ClientException
     * @throws ClientStatusException
     * @throws ClientTimeoutException
     */
    public function getMailHeader(int $message_number)
    {
        $header = new Header();
        $this->client->top($message_number, 0);
        do {
            $line = $this->client->receiveOneLine();
            if ($line === true) break;
            else {
                $header->inputLine($line);
            }
        } while (true);
        return $header->get();
    }

    /**
     * @param int $message_number
     * @return array|mixed
     * @throws ClientException
     * @throws ClientStatusException
     * @throws ClientTimeoutException
     */
    public function getMailHeaderParsed(int $message_number)
    {
        $header = new Header();
        $this->client->top($message_number, 0);
        do {
            $line = $this->client->receiveOneLine();
            if ($line === true) break;
            else {
                $header->inputLine($line);
            }
        } while (true);
        return $header->getParsed();
    }

    /**
     * @param int $message_number
     * @return string
     * @throws ClientException
     * @throws ClientStatusException
     * @throws ClientTimeoutException
     */
    public function getMailRaw(int $message_number) {
        $raw = "";
        $this->client->retrieve($message_number);
        do {
            $line = $this->client->receiveOneLine();
            if ($line === true) break;
            if ($raw !== "") $raw .= "\n";
            $raw .= $line;
        } while (true);
        return $raw;
    }

    /**
     * @param  int    $message_number
     * @param  string $file_name
     * @param  string $folder_name
     * @throws ClientException
     * @throws ClientStatusException
     * @throws ClientTimeoutException
     * @throws FileManagementException
     */
    public function saveMailRaw(
        int $message_number,
        string $file_name = 'mail.txt',
        string $folder_name = ""
    ) {
        $message_id_formatted = trim($this->getMailHeaderParsed($message_number)['message-id'], '<>');
        if (empty($folder_name)) {
            $folder_name = $this->mail_save_directory . str_replace(
                ['<', '>', ':', '"', '/', '\\', '|', '?', '*'],
                '_',
                $message_id_formatted
            );
        }
        $this->__makeSaveDirectory($folder_name);

        $this->client->retrieve($message_number);

        $file = fopen($folder_name . '/' . $file_name, 'w');
        if ($file) {
            do {
                $line = $this->client->receiveOneLine();
                if ($line === true) break;
                fwrite($file, $line . PHP_EOL);
            } while (true);
            fclose($file);
        } else {
            throw new FileManagementException('Failed to open the new file.');
        }
    }

    /**
     * @param  int $message_number
     * @return string
     * @throws ClientException
     * @throws ClientStatusException
     * @throws ClientTimeoutException
     */
    public function getMailBodyRaw(int $message_number)
    {
        $body_raw = "";
        $is_header = true;
        $this->client->retrieve($message_number);
        do {
            $line = $this->client->receiveOneLine();
            if ($line === true) break;
            else if ($is_header) {
                if ($line === "") $is_header = false;
            } else {
                if ($body_raw !== '') $body_raw .= "\n";
                $body_raw .= $line;
            }
        } while (true);
        return $body_raw;
    }

    /**
     * @param int $message_number
     * @param string $file_name
     * @param string|null $folder_name
     * @throws ClientException
     * @throws ClientStatusException
     * @throws ClientTimeoutException
     * @throws FileManagementException
     */
    public function saveMailBodyRaw(
        int $message_number,
        string $file_name = 'mail.body.txt',
        string $folder_name = ""
    ) {
        $message_id_formatted = trim($this->getMailHeaderParsed($message_number)['message-id'], '<>');
        if (empty($folder_name)) {
            $folder_name = $this->mail_save_directory . str_replace(
                    ['<', '>', ':', '"', '/', '\\', '|', '?', '*'],
                    '_',
                    $message_id_formatted
                );
        }
        $this->__makeSaveDirectory($folder_name);

        $this->client->retrieve($message_number);

        $file = fopen($folder_name . '/' . $file_name, 'w');
        if ($file) {
            do {
                $line = $this->client->receiveOneLine();
                if ($line === true) break;
                else if ($is_header) {
                    if ($line === "") $is_header = false;
                } else {
                    fwrite($file, $line . PHP_EOL);
                }
            } while (true);
        } else {
            throw new FileManagementException('Failed to open the new file.');
        }
    }
}
