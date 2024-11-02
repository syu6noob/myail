<?php

declare(strict_types=1);

namespace Myail\Client;

use Myail\Exception\Client\ClientException;
use Myail\Exception\Client\ClientTimeoutException;
use Myail\Exception\Client\ClientConnectException;

abstract class AbstractClient
{
    /** @var string $host including the port number */
    protected $host = "";

    /** @var resource|false $socket */
    protected $socket = null;

    /** @var array $options SSL context options */
    protected $option;

    /**
     * Set Hostname and Port
     * @param string $hostname Hostname
     * @param int $port Port number
     * @param ?bool $is_ssl Is SSL
     * @param int $timeout
     * @param array $options
     */
    public function __construct(
        string $hostname,
        int    $port,
        bool   $is_ssl = false,
        int    $timeout = 10,
        array  $options = []
    )
    {
        $this->host = ($is_ssl ? 'ssl://' : '') . $hostname . ':' . $port;
        $this->timeout = $timeout;
        $this->options = $options;
    }

    /**
     * 接続を確立する
     * @throws ClientConnectException 接続エラーが発生した場合
     */
    protected function __open()
    {
        // 一時的にエラーハンドラーを設定
        set_error_handler(function($error_number, $error_string) {
            throw new \RuntimeException($error_string, $error_number);
        });

        try {
            $this->socket = stream_socket_client(
                $this->host,
                $error_number,
                $error_string,
                $this->timeout,
                STREAM_CLIENT_CONNECT,
                stream_context_create($this->options)
            );
            if ($this->socket === false) {
                throw new ClientConnectException("Could not connect to $this->host");
            } else {
                stream_set_timeout($this->socket, $this->timeout);
            }
        } catch (\RuntimeException $e) {
            throw new ClientConnectException("Could not connect to $this->host : " . $e->getMessage(), $e->getCode(), $e);
        } finally {
            restore_error_handler();
        }
    }

    /**
     * タイムアウトかを検証
     * @throws ClientTimeoutException 接続エラーが発生した場合
     */
    protected function __judgeIsTimeout(): bool
    {
        $info = stream_get_meta_data($this->socket);
        if ($info['timed_out']) {
            throw new ClientTimeoutException("Connection timed out while waiting for server response.");
        } {
            return true;
        }
    }

    /**
     * コマンドを送信
     * @param  string $command
     * @throws ClientException
     */
    protected function __sendCommand(string $command)
    {
        if ($this->socket) {
            $result = fwrite($this->socket, $command . "\r\n");
            if ($result === false) {
                throw new ClientException("Failed to send command: '$command'");
            }
        } else {
            throw new ClientException("Unable to connect to the server");
        }
    }

    /**
     * レスポンスを1行受信
     */
    public function receiveOneLine()
    {
        return stream_get_line($this->socket, 1024, "\r\n");
    }

    /**
     * 接続を閉じる
     */
    protected function __close()
    {
        if (!$this->socket) {
            fclose($this->socket);
            $this->socket = null;
        }
    }
}
