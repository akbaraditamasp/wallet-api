<?php
require_once __DIR__ . "/../vendor/autoload.php";

use Lego\App;

$app = new App();

$app->mount("/api", function (App $app) {
    $app->mount("/admin", function (App $app) {
        $app->route("GET", "/login", "Controller\\Admin::login");
        $app->route("DELETE", "/logout", "Controller\\Admin::logout");
    });

    $app->mount("/user", function (App $app) {
        $app->route("DELETE", "/logout", "Controller\\User::logout");
        $app->route("GET", "/login", "Controller\\User::login");
        $app->route("GET", "/my", "Controller\\User::get");
        $app->route("GET", "/username/(\w+)", "Controller\\User::getByUsername");
        $app->route("GET", "/(\d+)", "Controller\\User::get");
        $app->route("DELETE", "/(\d+)", "Controller\\User::delete");
        $app->route("PUT", "/(\d+)", "Controller\\User::update");
        $app->route("GET", "/", "Controller\\User::index");
        $app->route("POST", "/", "Controller\\User::create");
    });

    $app->mount("/transaction", function (App $app) {
        $app->route("POST", "/callback", "Controller\\Transaction::callback");
        $app->route("POST", "/topup", "Controller\\Transaction::topup");
        $app->route("POST", "/send", "Controller\\Transaction::send");
        $app->route("POST", "/withdraw", "Controller\\Transaction::processWithdraw");
        $app->route("GET", "/withdraw", "Controller\\Transaction::getWithdraw");
        $app->route("GET", "/(\d+)", "Controller\\Transaction::get");
        $app->route("GET", "/", "Controller\\Transaction::index");
    });

    $app->mount("/stats", function (App $app) {
        $app->route("GET", "/transaction", "Controller\\Stats::transaction");
        $app->route("GET", "/", "Controller\\Stats::index");
    });
});

$app->run();
