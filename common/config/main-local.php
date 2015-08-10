<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=yiiforum',
            'username' => 'root',
            'password' => '123456',
            'charset' => 'utf8',
        ],
        'mail' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            'useFileTransport' => true,
        ],
        'request' => [
            'enableCookieValidation' => true,
            'cookieValidationKey' => 'arsenal',
        ],
    ],
];
