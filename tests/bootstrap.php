<?php
declare(strict_types=1);
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    $loader = include __DIR__ . '/../vendor/autoload.php';
    $loader->add('precore', __DIR__ . '/src');
}
Logger::configure(__DIR__ . '/src/resources/log4php.xml');
define('BASEDIR', __DIR__ . '/..');
