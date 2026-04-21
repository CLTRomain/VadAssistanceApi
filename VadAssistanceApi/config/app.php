<?php

use Cake\Cache\Engine\FileEngine;
use Cake\Database\Connection;
use Cake\Database\Driver\Mysql;
use Cake\Log\Engine\FileLog;
use Cake\Mailer\Transport\MailTransport;
use Cake\Mailer\Transport\SmtpTransport;

return [
    'debug' => true,

    'App' => [
        'namespace' => 'App',
        'encoding' => 'UTF-8',
        'defaultLocale' => 'en_US',
        'defaultTimezone' => 'UTC',
        'base' => false,
        'dir' => 'src',
        'webroot' => 'webroot',
        'wwwRoot' => WWW_ROOT,
        'JWTApiToken' => 'XMGAriLvIHnjy43hdaceKy+cjOH3Bhn7JDmv1/xqW78=',
        'fullBaseUrl' => 'http://37.187.156.198',
        'imageBaseUrl' => 'img/',
        'cssBaseUrl' => 'css/',
        'jsBaseUrl' => 'js/',
        'paths' => [
            'plugins' => [ROOT . DS . 'plugins' . DS],
            'templates' => [ROOT . DS . 'templates' . DS],
            'locales' => [RESOURCES . 'locales' . DS],
        ],
        'contracts' => [
            'v1' => [
                1  => ['electromenager' => 200, 'cumul' => 5],
                2  => ['electricite' => 300, 'cumul' => 5],
                3  => ['plomberie' => 300, 'cumul' => 5],
                4  => ['gaz' => 300, 'cumul' => 5],
                5  => ['electricite' => 300, 'plomberie' => 300, 'gaz' => 300, 'cumul' => 5],
                6  => ['electricite' => 300, 'plomberie' => 300, 'cumul' => 5],
                7  => ['electricite' => 300, 'plomberie' => 300, 'electromenager' => 200, 'cumul' => 5],
                8  => ['electricite' => 300, 'plomberie' => 300, 'gaz' => 300, 'electromenager' => 200, 'cumul' => 5],
                13 => ['connectes' => 600, 'cumul' => 1],
                14 => ['juridique' => 200, 'permis' => 200, 'cumul' => 1],
                15 => ['juridique' => 200, 'permis' => 200, 'cumul' => 1],
                16 => ['telephonie' => 200, 'cumul' => 1],
                17 => ['telephonie' => 200, 'cumul' => 1],
                21 => ['electricite' => 300, 'plomberie' => 300, 'gaz' => 300, 'electromenager' => 200, 'cumul' => 5],
            ]
        ],
    ],

    'Security' => [
        'salt' => 'a3f8d2e1c7b4a9f6e5d2c1b8a7f4e3d2c9b6a5f2e1d8c7b4a3f6e5d2c1b8a7f4',
    ],

    'Asset' => [],

    'Cache' => [
        'default' => [
            'className' => FileEngine::class,
            'path' => CACHE,
            'url' => null,
        ],
        '_cake_translations_' => [
            'className' => FileEngine::class,
            'prefix' => 'myapp_cake_translations_',
            'path' => CACHE . 'persistent' . DS,
            'serialize' => true,
            'duration' => '+1 years',
            'url' => null,
        ],
        '_cake_model_' => [
            'className' => FileEngine::class,
            'prefix' => 'myapp_cake_model_',
            'path' => CACHE . 'models' . DS,
            'serialize' => true,
            'duration' => '+1 years',
            'url' => null,
        ],
    ],

    'Error' => [
        'errorLevel' => E_ALL,
        'skipLog' => [],
        'log' => true,
        'trace' => true,
        'ignoredDeprecationPaths' => [],
        'traceFormat' => null,
    ],

    'Debugger' => [
        'editor' => 'phpstorm',
    ],

    'EmailTransport' => [
        'default' => [
            'className' => MailTransport::class,
            /*
             * The keys host, port, timeout, username, password, client and tls
             * are used in SMTP transports
             */
            'host' => 'localhost',
            'port' => 25,
            'timeout' => 30,
            /*
             * It is recommended to set these options through your environment or app_local.php
             */
            //'username' => null,
            //'password' => null,
            'client' => null,
            'tls' => false,
            'url' => env('EMAIL_TRANSPORT_DEFAULT_URL', null),
        ],
        'mailgun' => [
            'className' => SmtpTransport::class,
            'host'      => 'smtp.eu.mailgun.org',
            'port'      => 587,
            'timeout'   => 30,
            'username'  => '',
            'password'  => '',
            'tls'       => true,
        ],
    ],

    /*
     * Email delivery profiles
     *
     * Delivery profiles allow you to predefine various properties about email
     * messages from your application and give the settings a name. This saves
     * duplication across your application and makes maintenance and development
     * easier. Each profile accepts a number of keys. See `Cake\Mailer\Mailer`
     * for more information.
     */
    'Email' => [
        'default' => [
            'transport' => 'mailgun',
            'from' => ['noreply@mg2.vad-assistance.fr' => 'VAD Assistance'],
        ],
    ],


    'Datasources' => [
        'default' => [
            'className' => Connection::class,
            'driver' => Mysql::class,
            'persistent' => false,
            'timezone' => 'UTC',
            'encoding' => 'utf8mb4',
            'flags' => [],
            'cacheMetadata' => true,
            'log' => false,
            'quoteIdentifiers' => false,


            'host'     => '127.0.0.1',
            'port'     => '8889',
            'username' => 'root',
            'password' => 'root',
            'database' => 'extranet_vad',   
        ],
        'test' => [
            'className' => Connection::class,
            'driver' => Mysql::class,
            'persistent' => false,
            'timezone' => 'UTC',
            'encoding' => 'utf8mb4',
            'flags' => [],
            'cacheMetadata' => true,
            'quoteIdentifiers' => false,
            'log' => false,
        ],
    ],

    'Log' => [
        'debug' => [
            'className' => FileLog::class,
            'path' => LOGS,
            'file' => 'debug',
            'url' => null,
            'scopes' => null,
            'levels' => ['notice', 'info', 'debug'],
        ],
        'error' => [
            'className' => FileLog::class,
            'path' => LOGS,
            'file' => 'error',
            'url' => null,
            'scopes' => null,
            'levels' => ['warning', 'error', 'critical', 'alert', 'emergency'],
        ],
        'queries' => [
            'className' => FileLog::class,
            'path' => LOGS,
            'file' => 'queries',
            'url' => null,
            'scopes' => ['cake.database.queries'],
        ],
    ],

    'Session' => [
        'defaults' => 'php',
    ],

    'DebugKit' => [
        'forceEnable' => false,
        'safeTld' => null,
        'ignoreAuthorization' => false,
    ],

    'TestSuite' => [
        'errorLevel' => null,
        'fixtureStrategy' => null,
    ],
];
