<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Preview extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public  $timestamps  = false;
    protected $table = 'proview';
    protected $fillable = [
        'id', 'cid','oid', 'userid','desc','end_time','is_publish','name','publish_type','content',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];


}