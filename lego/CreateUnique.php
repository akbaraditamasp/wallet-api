<?php
namespace Lego;

use Carbon\Carbon;
use EndyJasmi\Cuid;
use Model\Withdraw;

class CreateUnique
{

    public static function randNum()
    {
        $key = random_int(0, 999999);
        $key = str_pad($key, 6, 0, STR_PAD_LEFT);
        return $key;
    }

    public static function create($model, $column)
    {
        $cuid = Cuid::cuid();

        while ($model::where($column, $cuid)->first()) {
            $cuid = Cuid::cuid();
        }

        return $cuid;
    }

    public static function withdrawCode($user_id)
    {
        $code = CreateUnique::randNum();
        while (Withdraw::where("code", $code)->whereDate("valid_before", ">", Carbon::now())->where("user_id", $user_id)->first()) {
            $code = CreateUnique::randNum();
        }

        return $code;
    }
}
