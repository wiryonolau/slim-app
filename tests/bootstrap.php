<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

require __DIR__ . '/../vendor/autoload.php';

function debug($output)
{
    echo "\n\n";
    print_r($output);
    echo "\n\n";
    ob_flush();
}
