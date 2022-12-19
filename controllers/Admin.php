<?php

namespace Controller;

use Lego\AdminAuth;
use Lego\App;
use Lego\Validation;
use Model\Admin as ModelAdmin;

class Admin
{
    public static function login(App $app)
    {
        $data = $app->request->getQueryParameters();

        Validation::validate($app, [
            "username" => "required",
            "password" => "required",
        ], $data);

        $admin = ModelAdmin::where("username", $data["username"])->first();

        if ($admin) {
            if (password_verify($data["password"], $admin->password)) {
                return AdminAuth::make($admin);
            }
        }

        $app->response->setStatus(401);
        return [
            "error" => "Unauthorized",
        ];
    }

    public static function logout(App $app)
    {
        $app->admin();

        $admin = $app->adminAuth;
        $app->adminAuth->delete();

        return $admin->admin->makeHidden("password")->toArray();
    }
}
