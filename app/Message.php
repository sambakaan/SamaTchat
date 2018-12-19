<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
      /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'content', 'from_id', 'to_id','recreated_at','read_at'
    ];

    protected $dates = ['created_at', 'read_at'];

    public $timestamps = false;

   	public function from() {
   		return $this->BelongsTo(User::class,'from_id');
   	}
}
