<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    /** @use HasFactory<\Database\Factories\TeamFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'strength',
    ];

    public function standings()
    {
        return $this->hasMany(Standing::class);
    }

    public function tournaments()
    {
        return $this->belongsToMany(Tournament::class);
    }
}
