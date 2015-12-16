#!/usr/bin/env php
<?php

foreach (array(__DIR__ . '/../../../../vendor/autoload.php', __DIR__ . '/../vendor/autoload.php', __DIR__ . '/vendor/autoload.php') as $file) {
    if (file_exists($file)) {
        define('SMOKE_COMPOSER_INSTALL', $file);
        break;
    }
}

unset($file);

if (!defined('SMOKE_COMPOSER_INSTALL')) {
    fwrite(STDERR,
        'You need to set up the project dependencies using the following commands:' . PHP_EOL .
        'wget http://getcomposer.org/composer.phar' . PHP_EOL .
        'php composer.phar install' . PHP_EOL
    );
    die(1);
}

$loader = require SMOKE_COMPOSER_INSTALL;

define('SMOKE_VERSION', '1.2.4');

$app = new \whm\Smoke\Cli\Application();
$app->run();
