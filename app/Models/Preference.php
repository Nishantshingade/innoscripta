<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Preference extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'type'];

    public function sources(){
        return $this->hasMany(PreferenceSource::class);
    }

    public function categories(){
        return $this->hasMany(PreferenceCategory::class);
    }

    public function authors(){
        return $this->hasMany(PreferenceAuthor::class);
    }

}
