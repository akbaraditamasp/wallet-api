<?php
namespace Model;

use Illuminate\Database\Eloquent\Model as BaseModel;

class UserLogin extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['cuid'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
