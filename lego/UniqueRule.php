<?php
namespace Lego;

use Rakit\Validation\Rule;

class UniqueRule extends Rule
{
    protected $message = ":attribute :value has been used";

    protected $fillableParams = ['table', 'column', 'except'];

    protected $pdo;

    public function check($value): bool
    {
        // make sure required parameters exists
        $this->requireParameters(['table', 'column']);

        // getting parameters
        $column = $this->parameter('column');
        $table = $this->parameter('table') . "::where";
        $except = $this->parameter('except');

        if ($except and $except == $value) {
            return true;
        }

        $data = $table($column, $value)->first();

        return $data ? false : true;
    }
}
