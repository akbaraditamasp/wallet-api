<?php
namespace Lego;

class Request extends \Sabre\HTTP\RequestDecorator

{
    private $post, $get, $files;

    public function __construct(...$params)
    {
        parent::__construct(...$params);

        if ($this->getHeader("Content-Type") === "application/json") {
            $this->setPostData(json_decode(file_get_contents('php://input'), true));
        }

        $this->post = $this->getPostData();
        $this->get = $this->getQueryParameters();
        $this->files = $this->getFiles();

    }

    public function getRawFiles()
    {
        return $_FILES;
    }

    public function getFiles()
    {
        $files = [];
        foreach ($_FILES as $field => $file) {
            if (is_array($file)) {
                $tmp = [];
                for ($i = 0; $i < count($file["tmp_name"]); $i++) {
                    $tmp[] = new File([
                        "tmp_name" => $file["tmp_name"][$i],
                        "type" => $file["type"][$i],
                        "size" => $file["size"][$i],
                        "full_path" => $file["full_path"][$i],
                    ]);
                }

                $files[$field] = $tmp;
            } else {
                $files[$field] = new File($file);
            }
        }

        return $files;
    }

    public function post($key, $default = null)
    {
        return isset($this->post[$key]) ? $this->post[$key] : $default;
    }

    public function get($key, $default = null)
    {
        return isset($this->get[$key]) ? $this->get[$key] : $default;
    }

    public function file($key, $default = null)
    {
        return isset($this->files[$key]) ? $this->files[$key] : $default;
    }

}
