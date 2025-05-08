<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostTranslation extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'locale',
        'title',
        'slug',
        'content'
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
