<?php
namespace Model;

use Illuminate\Database\Eloquent\Model as BaseModel;

class Admin extends BaseModel
{
    //
    public function logins()
    {
        return $this->hasMany(UserLogin::class);
    }
}
