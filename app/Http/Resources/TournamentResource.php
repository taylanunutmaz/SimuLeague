<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TournamentResource extends JsonResource
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
            'name' => $this->name,
            'status' => $this->status,
            'number_of_weeks' => $this->number_of_weeks,
            'last_played_week' => $this->last_played_week,
            'created_at' => $this->created_at,
            'teams' => $this->whenLoaded('teams', function () {
                return TeamResource::collection($this->teams);
            }),
            'matches' => $this->whenLoaded('matches', function () {
                return TheMatchResource::collection($this->matches);
            }),
            'standings' => $this->whenLoaded('standings', function () {
                return StandingResource::collection($this->standings);
            }),
        ];
    }
}
