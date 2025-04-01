<?php

namespace App\Models;

use App\Enums\TournamentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    /** @use HasFactory<\Database\Factories\TournamentFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'number_of_weeks',
        'last_played_week',
        'status',
    ];

    protected $casts = [
        'status' => TournamentStatus::class,
    ];

    public function teams()
    {
        return $this->belongsToMany(Team::class);
    }

    public function standings()
    {
        return $this->hasMany(Standing::class)
            ->orderBy('points', 'desc')
            ->orderBy('goal_difference', 'desc');
    }

    public function matches()
    {
        return $this->hasMany(TheMatch::class);
    }
}
