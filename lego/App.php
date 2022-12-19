<?php

namespace Lego;

use Bramus\Router\Router;
use Dotenv\Dotenv;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Sabre\HTTP\Response;
use Sabre\HTTP\Sapi;

class App
{
    private $router;
    public $request;
    public $response;
    public $payment;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . "/../");
        $dotenv->load();

        Eloquent::boot();

        $this->router = new Router();
        $this->request = new Request(Sapi::getRequest());
        $this->response = new Response();
        $this->payment = new Payment();

        $cors = new Cors();

        $this->router->before("GET|POST|PUT|DELETE|PATCH|OPTIONS", "/.*", $cors->middleware($this));
        $this->router->match("OPTIONS", "/.*", $cors->options($this));
    }

    public function validate($rules)
    {
        Validation::validate($this, $rules, $this->request->getPostData() + $this->request->getRawFiles());
    }

    public function set(string $key, $value)
    {
        $this->$key = $value;
    }

    public function auth($strict = true)
    {
        Auth::auth($this, $strict);
    }

    public function verifPin($pin)
    {
        Auth::verifPin($pin, $this);
    }

    public function admin($strict = true)
    {
        AdminAuth::auth($this, $strict);
    }

    public function run()
    {
        $this->router->run();
    }

    public function route($methods, $pattern, $callback)
    {
        $app = $this;
        return $this->router->match($methods, $pattern, function (...$params) use ($callback, $app) {
            $app->set("params", $params);

            $this->response->setStatus(200);
            try {
                $body = $callback($app);

                if ($body) {
                    $this->response->setHeader("Content-Type", "application/json");
                    $this->response->setBody(json_encode($body));
                }
            } catch (ModelNotFoundException $e) {
                $this->response->setStatus(404);
                $this->response->setBody(json_encode([
                    "error" => "Not Found",
                ]));
            }

            Sapi::sendResponse($this->response);
        });
    }

    public function finish()
    {
        Sapi::sendResponse($this->response);
    }

    public function before($methods, $pattern, $callback)
    {
        $app = $this;
        return $this->router->before($methods, $pattern, function () use ($callback, $app) {
            $callback($app);
        });
    }

    public function mount($pattern, $callback)
    {
        $app = $this;
        return $this->router->mount($pattern, function () use ($callback, $app) {
            $callback($app);
        });
    }

    public function getRouter()
    {
        return $this->router;
    }
}
