<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;

class Page extends Model implements TranslatableContract
{
    use HasFactory, Translatable;

    protected $fillable = [
        'cover_image',
        'status',
        'user_id'
    ];

    public $translatedAttributes = [
        'title',
        'slug',
        'content'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
