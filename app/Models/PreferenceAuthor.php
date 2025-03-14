<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PreferenceAuthor extends Model
{
    use HasFactory;
    protected $fillable = ['preference_id', 'author'];
    public $timestamps = false;
}
