<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TheMatchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'week' => $this->week,
            'home_score' => $this->home_score,
            'away_score' => $this->away_score,
            'is_played' => $this->is_played,
            'home_team' => $this->whenLoaded('homeTeam', function () {
                return TeamResource::make($this->homeTeam);
            }),
            'away_team' => $this->whenLoaded('awayTeam', function () {
                return TeamResource::make($this->awayTeam);
            }),
        ];
    }
}
