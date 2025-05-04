<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageTranslation extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'locale',
        'title',
        'slug',
        'content'
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}
