<?php

namespace Controller;

use Lego\App;
use Lego\Auth;
use Lego\Validation;
use Model\User as ModelUser;

class User
{
    public static function create(App $app)
    {
        $app->validate([
            "username" => "required|unique:Model\\User,username",
            "password" => "required",
            "name" => "required",
            "pin" => "required",
        ]);

        $user = new ModelUser();
        $user->username = $app->request->post("username");
        $user->password = password_hash($app->request->post("password"), PASSWORD_BCRYPT);
        $user->name = $app->request->post("name");
        $user->balance = 0;
        $user->pin = password_hash($app->request->post("pin"), PASSWORD_BCRYPT);

        $user->save();

        return Auth::make($user);
    }

    public static function index(App $app)
    {
        $app->admin();

        $offset = $app->request->get("page", 1) - 1 * 20;
        $q = $app->request->get("q", "");
        $users = ModelUser::where("username", "LIKE", "%$q%")->orderBy("created_at", "desc");

        return [
            "page_total" => $users->count(),
            "data" => $users->skip($offset)->take(20)->get()->makeHidden(["password"])->toArray(),
        ];
    }

    public static function update(App $app)
    {
        $app->admin(false);

        if (!isset($app->adminAuth)) {
            $app->auth();
        }

        $user = !isset($app->adminAuth) ? $app->user->user : ModelUser::findOrFail($app->params[0]);

        $app->validate([
            "username" => "unique:Model\\User,username," . $user->username,
        ]);

        $user->username = $app->request->post("username", $user->username);

        if (isset($app->adminAuth)) {
            if ($app->request->post("password")) {
                $user->password = password_hash($app->request->post("password"), PASSWORD_BCRYPT);
            }

            if ($app->request->post("pin")) {
                $user->pin = password_hash($app->request->post("pin"), PASSWORD_BCRYPT);
            }
        }

        $user->name = $app->request->post("name", $user->name);

        $user->save();

        return $user->makeHidden(["balance", "password", "pin"])->toArray();
    }

    public static function delete(App $app)
    {
        $app->admin();

        $user = ModelUser::findOrFail($app->params[0]);
        $user->delete();

        return $user->makeHidden(["balance", "password", "pin"])->toArray();
    }

    public static function login(App $app)
    {
        $data = $app->request->getQueryParameters();

        Validation::validate($app, [
            "username" => "required",
            "password" => "required",
        ], $data);

        $admin = ModelUser::where("username", $data["username"])->first();

        if ($admin) {
            if (password_verify($data["password"], $admin->password)) {
                return Auth::make($admin);
            }
        }

        $app->response->setStatus(401);
        return [
            "error" => "Unauthorized",
        ];
    }

    public static function logout(App $app)
    {
        $app->auth();

        $user = $app->user;
        $app->user->delete();

        return $user->user->makeHidden(["password", "balance"])->toArray();
    }

    public static function get(App $app)
    {
        $app->admin(false);

        if (!isset($app->adminAuth)) {
            $app->auth();
        }

        $user = isset($app->adminAuth) ? ModelUser::findOrFail($app->params[0]) : $app->user->user;

        return $user->makeHidden("password")->toArray();
    }

    public static function getByUsername(App $app)
    {
        $app->auth();

        $user = ModelUser::where("username", $app->params[0])->firstOrFail();

        return $user->makeHidden("password")->toArray();
    }
}
