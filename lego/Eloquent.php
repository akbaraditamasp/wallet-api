<?php

namespace Lego;

use Illuminate\Database\Capsule\Manager as Capsule;

class Eloquent
{
    public static function boot()
    {
        $capsule = new Capsule;

        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => $_ENV["DB_HOST"],
            'database' => $_ENV["DB_NAME"],
            'username' => $_ENV["DB_USER"],
            'password' => $_ENV["DB_PASS"],
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }
}
