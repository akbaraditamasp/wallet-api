<?php
namespace Lego;

class File
{
    public $tmp_name, $type, $size, $filename;

    public function __construct($file)
    {
        $this->tmp_name = $file["tmp_name"];
        $this->type = $file["type"];
        $this->size = $file["size"];
        $this->filename = $file["full_path"];
    }

    public function move($path, $name = null)
    {
        move_uploaded_file($this->tmp_name, $path . "/" . ($name ?? $this->filename));
    }
}
