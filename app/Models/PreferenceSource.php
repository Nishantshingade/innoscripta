<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PreferenceSource extends Model
{
    use HasFactory;
    protected $fillable = ['preference_id', 'source'];
    public $timestamps = false;
}
