<?php
namespace Model;

use Illuminate\Database\Eloquent\Model as BaseModel;

class Transaction extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        "title",
        "note",
        "type",
        "amount",
        "invoice_id",
        "status",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
