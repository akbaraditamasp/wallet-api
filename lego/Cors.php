<?php
namespace Lego;

use Medz\Cors\Cors as CorsLib;

class Cors
{
    private $config = [
        'allow-credentials' => false, // set "Access-Control-Allow-Credentials" 👉 string "false" or "true".
        'allow-headers' => ['*'], // ex: Content-Type, Accept, X-Requested-With
        'expose-headers' => ["*"],
        'origins' => ['*'], // ex: http://localhost
        'methods' => ['*'], // ex: GET, POST, PUT, PATCH, DELETE
        'max-age' => 86400,
    ];
    private $request, $response;

    public function __construct()
    {
        $this->request = $_REQUEST ?? [];
        $this->response = [];

        $cors = new CorsLib($this->config);
        $cors->setRequest('array', $this->request);
        $cors->setResponse('array', $this->response);
        $cors->handle();

        $this->response = $cors->getResponse();
    }

    public function middleware(App $app)
    {
        $response = $this->response;
        return function () use ($response, $app) {
            $app->response->setHeader("Access-Control-Allow-Origin", $response["Access-Control-Allow-Origin"] ?? "");
            $app->response->setHeader("Access-Control-Max-Age", $response["Access-Control-Max-Age"] ?? "");
        };
    }

    public function options(App $app)
    {
        $response = $this->response;
        return function () use ($response, $app) {
            $app->response->setStatus(200);
            $app->response->setHeader("Access-Control-Allow-Methods", $response["Access-Control-Allow-Methods"] ?? "");
            $app->response->setHeader("Access-Control-Allow-Headers", $response["Access-Control-Allow-Headers"] ?? "");

            $app->finish();
        };
    }
}
