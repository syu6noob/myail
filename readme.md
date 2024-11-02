<div align="center">
  
<h1>
  <img width="40%" src="/resources/myail_logo_text.svg" />
</h1>
  
<p>🐱 A Simple Email Client for PHP</p>
  
💡 [What is Myail?](#-what-is-myail) •
 ✨ [Feature](#-feature) •
 ⚡ [Requirement](#-requirement) •
 🛠️ [Contribution](#%EF%B8%8F-contribution) •
 💬 [Feedback](#-feedback) •
 📄 [LICENSE](#-license)

</div>

## 💡 What is Myail?

Myail is a lightweight email client for PHP 7.0 and above.  
This library provides methods that can be easily integrated into your projects. Myail doesn’t use the IMAP extension, making it slightly faster.  
The name "Myail" is inspired by a cat sound in Japanese "Mya~".  

## ✨ Feature

- **POP3 Server Support** - Connect to POP3 servers compliant with RFC 1939, RFC 2449, RFC 2045, and RFC 2047.
- **SSL Connection** - Supports both plaintext and SSL connections. You can also configure [SSL context options](https://www.php.net/manual/en/context.ssl.php) for various SSL connection types.
- **Mail Header Parser** - Format email headers automatically.
- **Mail Saving** - Save entire emails or just the body to a local directory. 

### 💫 Upcoming Features

- **SMTP Server Support**
- **IMAP Server Support**
- And more!  

If you'd like to help develop these features, contributions are welcome!

## ⚡ Requirement
- PHP >= 7.0
- ext-mbstring

## 🚀 Usage
For a complete example, please refer to [example.php](./resources/example.php).

### Installation

To install using Composer, run the following command:
```bash
$ composer require syu6noob/myail
```

### Initialization

In a PHP file (e.g., `index.php`) in the directory containing the `src/` folder, add the following code:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Myail\POP3;

try {
    $pop3 = new POP3([
        'hostname' => 'pop.example.com',
        'port' => 110, // use 995 for SSL
        'is_ssl' => false, // set to true for SSL
        'timeout' => 10, // optional
        'auth' => [
            'username' => 'username',
            'password' => 'password',
            `methods` => ['user', 'sasl_login', 'sasl_plain'] // optional
        ],
        'stream_options' => [
            // optional
            'ssl' => [
                'cafile' => __DIR__ . '/cacert.pem',
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ],
        'mail_save_directory' => __DIR__ . '/save/'
    ]);
    
    // Additional code here...
    
} catch (\Exception $ex) {
    echo $ex->getMessage();
}
```
#### Parameters
`*` is required parameter.
- `hostname`* - POP3 server hostname
- `port`* - POP3 server port number
- `is_ssl`* - Whether the connection uses SSL
- `auth` - Authorization options
  - `username`* - POP3 server username
  - `password`* - POP3 server password
  - `methods` - The order in which to execute authentication methods
- `timeout` -  Connection timeout in seconds
- `stream_options` - [SSL context options](https://www.php.net/manual/en/context.ssl.php) for secure connections.
- `mail_save_directory`* - Directory to save downloaded emails

----

### quit()

`quit()`

Closes the connection to the POP3 server and applies any pending deletions.

#### Example
```php
$pop3->quit();
```

----

### getStatus()

`getStatus(): array`  

Retrieve the total number of emails and total size on the server: 

#### Example
```php
$pop3->getStatus();
```
#### Returns
```php
array(2) [
    'count' => nn, # total number of emails
    'size'  => mm  # total size of all emails
]
```

----

### getMailSize()

`getMailSize(int $message_number): int`  

Retrieves the size of the specified email.

#### Example
```php
$pop3->getMailSize(10);
```
#### Returns
```php
XXXX # mail size
```

----

### getMailSizeList()

`getMailSizeList(int $max_count = 50): array`

Retrieves the size of the specified number of emails.

If `$max_count = -1`, the library retrieves the size of all emails on the server.

#### Example
```php
$pop3->getMailSizeList(-1);
```
#### Returns
```php
array(XX) [
    1 => XXXX,
    2 => XXXX,
    3 => XXXX,
    ...
]
```

----

### getMailUid()

`getMailUid(int $message_number): string`

Retrieves the uid (unique-id) of the specified email.

#### Example
```php
$pop3->getMailUid(10);
```
#### Returns
```php
XXXXXXXXXX # mail uid
```

----

### getMailUidList()

`getMailUidList(int $max_count = 50): array`

Retrieves the uid lists of the specified number of emails.

If `$max_count = -1`, the library retrieves the uid lists of all emails on the server.


#### Example
```php
$pop3->getMailUidlList(-1);
```
#### Returns
```php
array(XX) [
    1 => XXXX,
    2 => XXXX,
    3 => XXXX,
    ...
]
```

----

### deleteMail()

`deleteMail(int $message_number)`

Delete the specified email.

> [!NOTE]  
> This action marks the specified email for deletion.
> The email is actually deleted only when the connection is closed by calling quit().

#### Example
```php
$pop3->deleteMail(10);
```

----

### getMailHeader()

#### Example
`getMailHeaderRaw(int $message_number): string`

Fetch the header of specified email which is split into field name and value

#### Example
```php
$pop3->getMailHeaderRaw(1);
```
#### Return
```
array [
  'from' => 'John Doe <jdoe@machine.example>',
  'to' => 'Mary Smith <mary@example.net>',
  'subject' => 'Saying Hello',
  'date' => 'Fri, 21 Nov 1997 09:55:06 -0600',
  'message-id' => '<1234@local.machine.example>'
]
```

----

### getMailHeaderParsed()

`getMailHeaderParsed(int $message_number): array`

Fetch the formated header of specified email.  
This includes fields such as:
- `To:`
- `Cc:`
- `Bcc:`
- `From:`
- `Sender:`
- `Subject:`
- `Data:`
- `Message-id:`
- `References:`
- `In-reply-to:`
- `Mime-version:`
- `Content-type:`
- `Content-transfer-encoding:`

#### Example
```php
$pop3->getMailHeaderParsed(2);
```
#### Returns
```php
# From: John Doe <jdoe@machine.example>
# To: Mary Smith <mary@example.net>
# Cc: A Group:Ed Jones <c@a.test>,joe@where.test,John <jdoe@one.test>;
# Bcc: Hidden recipients  :(nobody(that I know))  ;
# Subject: Saying Hello
# Date: Fri, 21 Nov 1997 09:55:06 -0600
# Message-ID: <1234@local.machine.example>

array [
    'from' => [
        0 => [
            'display_name' => 'John Doe',
            'address' => 'admin@onamae.com'
        ]
    ],
    'to' => [
        0 => [
            'display_name' => 'Mary Smith',
            'address' => 'mary@example.net'
        ]
    ],
    'cc' => [
        'mailboxes' => [
            [
                'display_name' => 'Ed Jones',
                'address' => "c@a.test"
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
    'bcc' => [
        'mailboxes' => [],
        'group_name' => 'Hidden recipients'
    ],
    'subject' => 'Saying Hello',
    'date' => 'Fri, 21 Nov 1997 09:55:06 -0600',
    'message-id' => '<1234@local.machine.example>'
]
```

----

### getMailBodyRaw()

`getMailBodyRaw(int $message_number): string`

Fetch the raw body of specified email.

#### Example
```php
$pop3->getMailBodyRaw(1);
```
#### Returns
```
This is a message just to say hello.
So, "Hello".
```

----

### getMailRaw()

`getMailRaw(int $message_number): string`

Fetch the entire contents of specified email, including both headers and body.

#### Example
```php
$pop3->getMailRaw(1);
```
#### Return
```
From: John Doe <jdoe@machine.example>
To: Mary Smith <mary@example.net>
Subject: Saying Hello
Date: Fri, 21 Nov 1997 09:55:06 -0600
Message-ID: <1234@local.machine.example>

This is a message just to say hello.
So, "Hello".
```

----

### getMailHeaderRaw()

#### Example
`getMailHeaderRaw(int $message_number): string`

Fetch the raw header of specified email.

#### Example
```php
$pop3->getMailHeaderRaw(1);
```
#### Return
```
From: John Doe <jdoe@machine.example>
To: Mary Smith <mary@example.net>
Subject: Saying Hello
Date: Fri, 21 Nov 1997 09:55:06 -0600
Message-ID: <1234@local.machine.example>
```

----

### getMailBodyRaw()

#### Example
`getMailBodyRaw(int $message_number): string`

Fetch the raw body of specified email.

#### Example
```php
$pop3->getMailBodyRaw(1);
```
#### Return
```
This is a message just to say hello.
So, "Hello".
```

## 🛠️ Contribution

**Myail is currently in beta.**  
Some features are still under development. If you’re interested in helping develop new features or improving existing ones, please feel free to open issues or submit pull requests.    

## 💬 Feedback

The author is new to open-source projects, PHP, and welcomes any feedback or suggestions.  
I would like you to share your thoughts by creating issues or pull requests.
You can also contact the author on [X](https://www.x.com/syu6noob/).

## 📄 LICENSE

This library is licensed under Apache License 2.0.  
If you include Myail in your own project, I would be happy to know about it!


