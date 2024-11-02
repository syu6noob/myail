<?php

declare(strict_types=1);

namespace Myail\Client;

use Myail\Exception\Client\ClientException;
use Myail\Exception\Client\ClientStatusException;
use Myail\Exception\Client\ClientTimeoutException;
use Myail\Exception\Client\ClientConnectException;
use Myail\Exception\Client\ClientAuthorizationException;
use Myail\Exception\Client\ClientServerResponseException;

const ENV_DISCONNECTED = 0;
const ENV_AUTHORIZATION = 1;
const ENV_TRANSACTION = 2;

final class POP3Client extends AbstractClient
{
    /** @var array|null $enabled_command_list */
    public $enabled_command_list;

    /** @var int $status ENV_DISCONNECTED|ENV_AUTHORIZATION|ENV_AUTHORIZATION */
    public $status = ENV_DISCONNECTED;

    /**
     * サーバに送ったコマンドが成功したのかを判断
     *
     * @param string $response
     * @return array
     * @throws ClientServerResponseException
     */
    private function __judgeIsSucceed(string $response): array
    {
//        var_dump($response);
        if (substr($response, 0, 3) === '+OK') {
            $message = substr($response, 4);
            return [true, $message];
        } else if (substr($response, 0, 4) === '-ERR') {
            $message = substr($response, 5);
            return [false, $message];
        } else {
            throw new ClientServerResponseException(
                "Detect unexpected Response: '$response'"
            );
        }
    }

    /**
     * サーバのレスポンスがタイムアウトしたかを判定
     * @return bool
     * @throws ClientTimeoutException
     */
    protected function __judgeIsTimeout(): bool
    {
        if (!parent::__judgeIsTimeout()) $this->status = ENV_DISCONNECTED;;
        return true;
    }

    /**
     * サーバからの応答を一行ずつ取得
     * @return string|true
     * @throws ClientTimeoutException|ClientStatusException
     */
    public function receiveOneLine()
    {
        if ($this->status === ENV_AUTHORIZATION || ENV_TRANSACTION) {
            $line = stream_get_line($this->socket, 1024, "\r\n");
//            var_dump($line);
            if ($line === ".") return true;
            if ($line === "..") return ltrim($line, '.');
            $this->__judgeIsTimeout();
            return $line;
        } else {
            throw new ClientStatusException();
        }
    }

    /**
     * サーバからの応答をすべて受信し、一行ごとに配列に格納
     * @param  ?bool $response_is_one_line
     * @return array
     * @throws ClientServerResponseException
     * @throws ClientTimeoutException
     * @throws ClientStatusException
     */
    protected function __receiveAllResponse(
        bool $response_is_one_line = true
    ): array
    {
        if ($this->status === ENV_TRANSACTION) {
            $response = [];

            // First Line
            $first_line = $this->receiveOneLine();
            list($is_success, $response[]) = $this->__judgeIsSucceed($first_line);

            // After ...
            if ($is_success) {
                if (!$response_is_one_line) {
                    do {
                        $line = $this->receiveOneLine();
                        if ($line === true) break;
                        $response[] = $line;
                    } while (true);
                }
                return [true, $response];
            } else {
                return [false, $response];
            }
        } else {
            throw new ClientStatusException();
        }
    }

    //-----------------------------------------------------
    // RFC 1939
    //-----------------------------------------------------

    /**
     * サーバとの接続を開く
     * @throws ClientConnectException
     * @throws ClientTimeoutException
     * @throws ClientServerResponseException
     * @throws ClientStatusException
     */
    public function open()
    {
        if ($this->status === ENV_DISCONNECTED) {
            parent::__open();
            $line = $this->receiveOneLine();
            list($is_success, $response) = $this->__judgeIsSucceed($line);
            // var_dump($response);
            if ($is_success) {
                $this->status = ENV_AUTHORIZATION;
            } else {
                throw new ClientConnectException("Status command failed with response: $response");
            }
        } else {
            throw new ClientStatusException("Unable to execute USER command in the current status.");
        }
    }

    /**
     * サーバにログインする関数
     * @param string $username
     * @param string $password
     * @return bool
     * @throws ClientException
     * @throws ClientServerResponseException
     * @throws ClientStatusException
     * @throws ClientTimeoutException
     */
    public function auth(string $username, string $password): bool
    {
        if ($this->status === ENV_AUTHORIZATION) {
            $this->__sendCommand("USER $username");
            $line = $this->receiveOneLine();
            list($is_success_user) = $this->__judgeIsSucceed($line);
            if (!$is_success_user) {
                return false;
            }

            $this->__sendCommand("PASS $password");
            $line = $this->receiveOneLine();
            list($is_success_pass) = $this->__judgeIsSucceed($line);
            if (!$is_success_pass) {
                return false;
            }

            $this->status = ENV_TRANSACTION;
            return true;
        } else {
            throw new ClientStatusException("Unable to execute USER command in the current status. $this->status");
        }
    }

    /**
     * サーバのメッセージの数とサイズを取得
     * @throws ClientException
     * @return array
     */
    public function status(): array
    {
        if ($this->status === ENV_TRANSACTION) {
            $this->__sendCommand("STAT");
            $line = $this->receiveOneLine();
            // var_dump($line);
            list($is_success, $response) = $this->__judgeIsSucceed($line);
            if ($is_success) {
                list($count, $size) = explode(' ', $response);
                return [
                    'count' => intval($count),
                    'size' => intval($size)
                ];
            } else {
                throw new ClientException("Status command failed with response: $response");
            }
        } else {
            throw new ClientStatusException();
        }
    }

    /**
     * メッセージのサイズの一覧を表示
     * @return array
     * @throws ClientException
     */
    public function list(): array
    {
        if ($this->status === ENV_TRANSACTION) {
            $this->__sendCommand("LIST");
            $line = $this->receiveOneLine();
            list($is_success, $response) = $this->__judgeIsSucceed($line);

            if ($is_success) {
                $list = [];
                do {
                    $line = $this->receiveOneLine();
                    if ($line === true) {
                        break;
                    } else {
                        list($number, $size) = explode(' ', $line);
                        $list[intval($number)] = intval($size);
                    }
                } while (true);
                return $list;
            } else {
                throw new ClientException("LIST command failed with response: $response[0]");
            }
        } else {
            throw new ClientStatusException();
        }
    }

    /**
     * 特定のメッセージのサイズを表示
     * @param  int $message_number
     * @return array
     * @throws ClientException
     * @throws ClientConnectException
     * @throws ClientServerResponseException
     */
    public function listMsg(int $message_number): array
    {
        if ($this->status === ENV_TRANSACTION) {
            $this->__sendCommand("LIST $message_number");

            $line = $this->receiveOneLine();
            list($is_success, $response) = $this->__judgeIsSucceed($line);

            if ($is_success) {
                list($number, $size) = explode(' ', $response);
                $list[intval($number)] = intval($size);
                return $list;
            } else {
                throw new ClientException("LIST command failed with response: $response");
            }
        } else {
            throw new ClientStatusException();
        }
    }

    /**
     * メール本文を取得しようとする
     * 成功したか否かのみを返すので、本文の取得はreceiveOneLine()関数を用いて、一行ずつ取得する
     * ※メモリリークを回避するため
     * @param  int  $message_number
     * @return bool
     * @throws ClientException
     * @throws ClientStatusException
     */
    function retrieve(int $message_number = null): bool
    {
        if ($this->status === ENV_TRANSACTION) {
            $this->__sendCommand("RETR $message_number");

            $line = $this->receiveOneLine();
            list($is_success, $response) = $this->__judgeIsSucceed($line);

            if ($is_success) {
                return $is_success;
            } else {
                throw new ClientException("RETR command failed with response: $response");
            }
        } else {
            throw new ClientStatusException();
        }
    }

    /**
     * メッセージを削除
     * @param int $id
     * @return mixed
     * @throws ClientException
     * @throws ClientServerResponseException
     * @throws ClientStatusException
     * @throws ClientTimeoutException
     */
    public function delete(int $id)
    {
        if ($this->status === ENV_TRANSACTION) {
            $this->__sendCommand("DELE $id");
            $line = $this->receiveOneLine();
            list($is_success, $response) = $this->__judgeIsSucceed($line);
            if ($is_success) {
                return $is_success;
            } else {
                throw new ClientException("LIST command failed with response: $response");
            }
        } else {
            throw new ClientStatusException();
        }
    }

    /**
     * NOOPコマンド
     * @throws ClientConnectException
     * @throws ClientTimeoutException
     * @throws ClientException
     */
    function noop()
    {
        if ($this->status === ENV_TRANSACTION) {
            $this->__sendCommand("NOOP");
            $this->receiveOneLine();
        }
    }

    /**
     * サーバからログアウト
     * @throws ClientException
     */
    public function quit()
    {
        if ($this->status !== ENV_DISCONNECTED) {
            $this->__sendCommand("QUIT");
            $this->status = ENV_DISCONNECTED;
        } else {
            throw new ClientStatusException();
        }
    }

    //-----------------------------------------------------
    // RFC 2449
    //-----------------------------------------------------

    /**
     * @throws ClientServerResponseException
     * @throws ClientConnectException
     * @throws ClientStatusException
     * @throws ClientTimeoutException
     * @throws ClientException
     */
    public function capacity()
    {
        if ($this->status === ENV_AUTHORIZATION || ENV_TRANSACTION) {
            $this->__sendCommand("CAPA");
            $line = $this->receiveOneLine();
            list($is_success) = $this->__judgeIsSucceed($line);
            if ($is_success) {
                do {
                    $line = $this->receiveOneLine();
                    if ($line === true) break;
                    else {
                        $line = explode(' ', $line);
                        $this->enabled_command_list[$line[0]] = array_slice($line, 1);
                    }
                } while (true);
            }
            // 失敗しても何もしない
        } else {
            throw new ClientStatusException();
        }
    }

    /**
     * メール本文の一部を取得しようとする
     * 成功したか否かのみを返すので、本文の取得はreceiveOneLine()関数を用いて、一行ずつ取得する
     * ※メモリリークを回避するため
     * @param  int  $message_number
     * @param  int  $lines
     * @return bool
     * @throws ClientException
     * @throws ClientStatusException
     */
    function top(int $message_number, int $lines): bool
    {
        if ($this->status === ENV_TRANSACTION) {
            $this->__sendCommand("TOP $message_number $lines");

            $line = $this->receiveOneLine();
            list($is_success, $response) = $this->__judgeIsSucceed($line);

            if ($is_success) {
                return $is_success;
            } else {
                throw new ClientException("TOP command failed with response: $response");
            }
        } else {
            throw new ClientStatusException();
        }
    }

    /**
     * メッセージのUIDLの一覧を表示
     * @return array
     * @throws ClientException
     */
    public function uidl(): array
    {
        if ($this->status === ENV_TRANSACTION) {
            $this->__sendCommand("UIDL");
            $line = $this->receiveOneLine();
            list($is_success, $response) = $this->__judgeIsSucceed($line);

            if ($is_success) {
                $list = [];
                do {
                    $line = $this->receiveOneLine();
                    if ($line === true) {
                        break;
                    } else {
                        list($number, $uidl) = explode(' ', $line);
                        $list[intval($number)] = $uidl;
                    }
                } while (true);
                return $list;
            } else {
                throw new ClientException("UIDL command failed with response: $response");
            }
        } else {
            throw new ClientStatusException();
        }
    }

    /**
     * 特定のメッセージのUIDLを表示
     * @param  int $message_number
     * @return array
     * @throws ClientException
     * @throws ClientConnectException
     * @throws ClientServerResponseException
     */
    public function uidlMsg(int $message_number): array
    {
        if ($this->status === ENV_TRANSACTION) {
            $this->__sendCommand("UIDL $message_number");

            $line = $this->receiveOneLine();
            list($is_success, $response) = $this->__judgeIsSucceed($line);

            if ($is_success) {
                list($number, $uidl) = explode(' ', $response);
                $list[intval($number)] = $uidl;
                var_dump($list);
                return $list;
            } else {
                throw new ClientException("UIDL command failed with response: $response");
            }
        } else {
            throw new ClientStatusException();
        }
    }

    //-----------------------------------------------------
    // SASL
    //-----------------------------------------------------

    /**
     * ログインできる方法を表示
     * @return array
     * @throws ClientConnectException
     * @throws ClientException
     * @throws ClientServerResponseException
     * @throws ClientStatusException
     * @throws ClientTimeoutException
     */
    public function getAuthMethodList(): array
    {
        if ($this->status === ENV_AUTHORIZATION) {
            $list = [];
            $this->capacity();

            if (isset($this->enabled_command_list['USER'])) {
                $list[] = 'user';
            }

            $sasl = $this->enabled_command_list['SASL'];
            if (isset($sasl)) {
                foreach ($sasl as $method) {
                    switch ($method) {
                        case 'PLAIN':
                            $list[] = 'sasl_plain';
                            break;
                        case 'LOGIN':
                            $list[] = 'sasl_login';
                            break;
                    }
                }
            }

            return $list;
        } else {
            throw new ClientStatusException();
        }
    }

    /**
     * SASL PLAIN でサーバにログインする関数
     * @param  string $username
     * @param  string $password
     * @return bool
     * @throws ClientAuthorizationException
     * @throws ClientException
     * @throws ClientServerResponseException
     * @throws ClientTimeoutException
     */
    public function authPlain(string $username, string $password): bool
    {
        if ($this->status === ENV_AUTHORIZATION) {
            $authString = base64_encode("\0" . $username . "\0" . $password);
            $this->__sendCommand("AUTH PLAIN $authString");
            $line = $this->receiveOneLine();
            list($is_success) = $this->__judgeIsSucceed($line);
            if ($is_success) {
                $this->status = ENV_TRANSACTION;
                return true;
            } else {
                return false;
            }
        } else {
            throw new ClientStatusException();
        }
    }

    /**
     * SASL LOGIN でサーバにログインする関数
     * @param string $username
     * @param string $password
     * @return bool
     * @throws ClientException
     * @throws ClientServerResponseException
     * @throws ClientStatusException
     * @throws ClientTimeoutException
     */
    public function authLogin(string $username, string $password): bool
    {
        if ($this->status === ENV_AUTHORIZATION) {
            $this->__sendCommand("AUTH LOGIN");

            // `+ VXNlcm5hbWU6`(Username:) のような反応があるので一行受信
            $this->receiveOneLine();

            $this->__sendCommand(base64_encode($username));

            // `+ UGFzc3dvcmQ6`(Password:) のような反応があるので一行受信
            $this->receiveOneLine();

            $this->__sendCommand(base64_encode($password));

            $line = $this->receiveOneLine();
            var_dump($line);
            flush();
            list($is_success) = $this->__judgeIsSucceed($line);
            if ($is_success) {
                $this->status = ENV_TRANSACTION;
                return true;
            } else {
                return false;
            }
        } else {
            throw new ClientStatusException();
        }
    }
}
