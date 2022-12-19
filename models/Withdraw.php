<?php
namespace Model;

use Illuminate\Database\Eloquent\Model as BaseModel;

class Withdraw extends BaseModel
{
    //
    protected $dates = ["valid_before", "created_at", "updated_at"];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
