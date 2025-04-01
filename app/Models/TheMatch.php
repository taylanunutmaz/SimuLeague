<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TheMatch extends Model
{
    /** @use HasFactory<\Database\Factories\TheMatchFactory> */
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'home_team_id',
        'away_team_id',
        'home_score',
        'away_score',
        'week',
        'is_played',
    ];

    protected $casts = [
        'is_played' => 'boolean',
    ];

    public function homeTeam()
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam()
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function standings()
    {
        return $this->hasMany(Standing::class);
    }

    public function homeStanding()
    {
        return $this->hasOne(Standing::class, 'team_id', 'home_team_id')
            ->where('tournament_id', $this->tournament_id);
    }

    public function awayStanding()
    {
        return $this->hasOne(Standing::class, 'team_id', 'away_team_id')
            ->where('tournament_id', $this->tournament_id);
    }

    public function scopeOfTeam($query, $teamId)
    {
        return $query->where(function ($query) use ($teamId) {
            $query->where('home_team_id', $teamId)
                ->orWhere('away_team_id', $teamId);
        });
    }

    public function scopePlayed($query)
    {
        return $query->where('is_played', true);
    }

    public function scopeUnplayed($query)
    {
        return $query->where('is_played', false);
    }
}
