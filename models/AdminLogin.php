<?php
namespace Model;

use Illuminate\Database\Eloquent\Model as BaseModel;

class AdminLogin extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['cuid'];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
