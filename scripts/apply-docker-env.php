<?php

$path = dirname(__DIR__).DIRECTORY_SEPARATOR.'.env';
$c = file_get_contents($path);

$pairs = [
    'APP_NAME' => '"CRM System"',
    'DB_CONNECTION' => 'pgsql',
    'DB_HOST' => '127.0.0.1',
    'DB_PORT' => '5433',
    'DB_DATABASE' => 'crm_database',
    'DB_USERNAME' => 'postgres',
    'DB_PASSWORD' => 'secret',
    'MAIL_MAILER' => 'smtp',
    'MAIL_HOST' => '127.0.0.1',
    'MAIL_PORT' => '1025',
];

foreach ($pairs as $k => $v) {
    if (preg_match('/^#?'.preg_quote($k, '/').'=/m', $c)) {
        $c = preg_replace('/^#?'.preg_quote($k, '/').'=.*$/m', $k.'='.$v, $c);
    } else {
        $c = rtrim($c).PHP_EOL.$k.'='.$v.PHP_EOL;
    }
}

file_put_contents($path, $c);
echo "ENV_DOCKER_READY\n";
