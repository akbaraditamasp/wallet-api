<?php
namespace Lego;

use EndyJasmi\Cuid;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Model\User;
use Model\UserLogin;

class Auth
{
    public static function make(User $user)
    {
        $cuid = Cuid::make();

        $user->logins()->save(new UserLogin([
            "cuid" => $cuid,
        ]));

        $token = JWT::encode([
            "cuid" => $cuid,
        ], $_ENV["JWT_KEY"], "HS256");

        return $user->makeHidden(["password", "balance", "pin"])->toArray() + ["token" => $token];
    }

    public static function auth(App $app, $strict = true)
    {
        $request = $app->request;
        $response = $app->response;

        $bearer = $request->getHeader("Authorization");
        $bearer = explode(" ", $bearer)[1];
        $fail = true;

        if ($bearer) {
            try {
                $decoded = JWT::decode($bearer, new Key($_ENV["JWT_KEY"], 'HS256'));
                $decoded = (array) $decoded;
            } catch (\Exception$e) {
                $decoded = null;
            }

            if ($decoded) {
                $logins = UserLogin::where("cuid", $decoded["cuid"])->first();

                if ($logins) {
                    $app->set("user", $logins);
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

    public static function verifPin($pin, App $app)
    {
        if (!password_verify($pin, $app->user->user->pin)) {
            $app->response->setStatus(403);
            $app->finish();
            exit();
        }
    }
}
