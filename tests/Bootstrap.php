<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../Includes/Autoloader.php';

use Mocks\WpdbMock;

$loader = new Autoloader();
$loader->register();
$loader->addNamespace('SociallymapConnect', __DIR__ . '/../');
$loader->addNamespace('Mocks', __DIR__ . '/Mocks');

$wpdb = new WpdbMock();

function dbDelta()
{
    echo 'dbDelta';

    return [];
}