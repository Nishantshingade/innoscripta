<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $table = 'news';
    protected $fillable = [
        'type','source','category','title','url','author','published_at'
    ];
    protected $casts = ['published_at' => 'datetime'];
}
