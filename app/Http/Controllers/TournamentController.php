<?php

namespace App\Http\Controllers;

use App\Http\Resources\TeamResource;
use App\Http\Resources\TournamentResource;
use App\Models\Tournament;
use App\Services\FixtureService;
use Inertia\Inertia;

class TournamentController extends Controller
{
    public function index()
    {
        $tournaments = Tournament::paginate()
            ->withQueryString();

        return Inertia::render('Tournaments/Index', [
            'tournaments' => TournamentResource::collection($tournaments),
        ]);
    }

    public function show(Tournament $tournament)
    {
        return Inertia::render('Tournaments/Show', [
            'tournament' => TournamentResource::make($tournament->load('teams')),
        ]);
    }

    public function create()
    {
        return Inertia::render('Tournaments/Create', [
            'teams' => TeamResource::collection(
                \App\Models\Team::all()
            ),
        ]);
    }

    public function store()
    {
        $data = request()->validate([
            'name' => 'required|string|max:255|unique:tournaments,name',
            'team_ids' => 'required|array|min:2',
            'team_ids.*' => 'exists:teams,id',
        ]);

        /** @var Tournament $tournament */
        $tournament = Tournament::create($data);
        $tournament->teams()->attach($data['team_ids']);

        $tournament->save();

        return redirect()
            ->route('tournaments.index')
            ->with('success', 'Tournament created successfully.');
    }

    public function destroy(Tournament $tournament)
    {
        $tournament->delete();

        return redirect()
            ->route('tournaments.index')
            ->with('success', 'Tournament deleted successfully.');
    }

    public function fixtures(Tournament $tournament)
    {
        return Inertia::render('Tournaments/Fixtures', [
            'tournament' => TournamentResource::make($tournament->load('matches', 'matches.homeTeam', 'matches.awayTeam')),
        ]);
    }

    public function generateFixtures(Tournament $tournament, FixtureService $fixtureService)
    {
        try {
            $fixtureService->generateFixtures($tournament);
        } catch (\Exception $e) {
            return redirect()
                ->route('tournaments.fixtures', $tournament)
                ->with('error', 'Error generating fixtures: '.$e->getMessage());
        }

        return redirect()
            ->route('tournaments.fixtures', $tournament)
            ->with('success', 'Fixtures generated successfully.');
    }
}
