<?php
namespace Lego;

use Rakit\Validation\Validator;

class Validation
{
    public static function validate(App $app, $rules, $data)
    {
        $validator = new Validator;

        $validator->addValidator('unique', new UniqueRule());

        // make it
        $validation = $validator->make($data, $rules);

        $validation->validate();

        if ($validation->fails()) {
            $errors = $validation->errors();

            $app->response->setStatus(400);
            $app->response->setHeader("Content-Type", "application/json");
            $app->response->setBody(json_encode($errors->firstOfAll()));
            $app->finish();

            exit();
        }
    }
}
