<?php
namespace Model;

use Illuminate\Database\Eloquent\Model as BaseModel;

class User extends BaseModel
{
    public function logins()
    {
        return $this->hasMany(UserLogin::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function withdraws()
    {
        return $this->hasMany(Withdraw::class);
    }
}
