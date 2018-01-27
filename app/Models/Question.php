<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'question_type', 'level_type', 'tag_id', 'title', 'body', 'answer', 'answer_comment'
    ];
}
