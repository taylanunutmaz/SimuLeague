<?php

namespace App\Http\Controllers;

use App\Http\Resources\StandingResource;
use App\Http\Resources\TournamentResource;
use App\Models\Tournament;
use App\Services\PredictionService;
use App\Services\SimulationService;
use App\Services\StandingService;

class SimulationController extends Controller
{
    public function index(Tournament $tournament, PredictionService $predictionService)
    {
        $predictedChampionshipRates = $predictionService->predictChampionshipRates($tournament);

        return inertia('Tournaments/Simulation/Index', [
            'tournament' => TournamentResource::make($tournament->load('matches', 'matches.homeTeam', 'matches.awayTeam')),
            'standings' => StandingResource::collection($tournament->standings->load('team')),
            'predicted_championship_rates' => $predictedChampionshipRates,
        ]);
    }

    public function start(Tournament $tournament, StandingService $standingService)
    {
        $standingService->createStandings($tournament);

        return redirect()
            ->route('tournaments.simulation.index', $tournament)
            ->with('success', 'Simulation started successfully.');
    }

    public function playAllWeeks(Tournament $tournament, SimulationService $simulationService)
    {
        $simulationService->playAllWeeks($tournament);

        return redirect()
            ->route('tournaments.simulation.index', $tournament)
            ->with('success', 'All weeks played successfully.');
    }

    public function playNextWeek(Tournament $tournament, SimulationService $simulationService)
    {
        $simulationService->playNextWeek($tournament);

        return redirect()
            ->route('tournaments.simulation.index', $tournament)
            ->with('success', 'Next week played successfully.');
    }

    public function reset(Tournament $tournament, SimulationService $simulationService)
    {
        $simulationService->resetData($tournament);

        return redirect()
            ->route('tournaments.simulation.index', $tournament)
            ->with('success', 'Simulation reset successfully.');
    }
}
