<?php
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    $loader = include __DIR__ . '/../vendor/autoload.php';
    $loader->add('precore', __DIR__ . '/src');
}
