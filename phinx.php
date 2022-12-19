<?php

require_once "./vendor/autoload.php";
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

return
    [
    'paths' => [
        'migrations' => __DIR__ . '/db/migrations',
        'seeds' => __DIR__ . '/db/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'production' => [
            'adapter' => 'mysql',
            'host' => $_ENV["DB_HOST"],
            'name' => $_ENV["DB_NAME"],
            'user' => $_ENV["DB_USER"],
            'pass' => $_ENV["DB_PASS"],
            'port' => '3306',
            'charset' => 'utf8',
        ],
        'development' => [
            'adapter' => 'mysql',
            'host' => $_ENV["DB_HOST"],
            'name' => $_ENV["DB_NAME"],
            'user' => $_ENV["DB_USER"],
            'pass' => $_ENV["DB_PASS"],
            'port' => '3306',
            'charset' => 'utf8',
        ],
        'testing' => [
            'adapter' => 'mysql',
            'host' => 'localhost',
            'name' => 'testing_db',
            'user' => 'root',
            'pass' => '',
            'port' => '3306',
            'charset' => 'utf8',
        ],
    ],
    'version_order' => 'creation',
];
