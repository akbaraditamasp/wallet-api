<?php

namespace Lego;

use EndyJasmi\Cuid;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Model\Admin as ModelAdmin;
use Model\AdminLogin;

class AdminAuth
{
    public static function make(ModelAdmin $admin)
    {
        $cuid = Cuid::make();

        $admin->logins()->save(new AdminLogin([
            "cuid" => $cuid,
        ]));

        $token = JWT::encode([
            "cuid" => $cuid,
        ], $_ENV["JWT_KEY"], "HS256");

        return $admin->makeHidden("password")->toArray() + ["token" => $token];
    }

    public static function auth(App $app, $strict = true)
    {
        $request = $app->request;
        $response = $app->response;

        $bearer = $request->getHeader("Authorization");
        $bearer = explode(" ", $bearer)[1] ?? "";
        $fail = true;

        if ($bearer) {
            try {
                $decoded = JWT::decode($bearer, new Key($_ENV["JWT_KEY"], 'HS256'));
                $decoded = (array) $decoded;
            } catch (\Exception $e) {
                $decoded = null;
            }

            if ($decoded) {
                $logins = AdminLogin::where("cuid", $decoded["cuid"])->first();

                if ($logins) {
                    $app->set("adminAuth", $logins);
                    $fail = false;
                }
            }
        }

        if ($fail && $strict) {
            $response->setStatus(401);
            $app->finish();

            exit();
        }
    }
}
