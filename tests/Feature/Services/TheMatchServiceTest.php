<?php

namespace Tests\Feature\Services;

use App\Models\Team;
use App\Models\TheMatch;
use App\Models\Tournament;
use App\Services\StandingService;
use App\Services\TheMatchService;
use App\Services\TournamentService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class TheMatchServiceTest extends TestCase
{
    protected $standingService;
    protected $tournamentService;
    protected TheMatchService $matchService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->standingService = Mockery::mock(StandingService::class);
        $this->tournamentService = Mockery::mock(TournamentService::class);

        $this->matchService = new TheMatchService($this->standingService, $this->tournamentService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_remaining_matches_returns_unplayed_matches_for_team()
    {
        $tournament = Tournament::factory()->create();
        $team1 = Team::factory()->create();
        $team2 = Team::factory()->create();
        $team3 = Team::factory()->create();
        $tournament->teams()->attach([$team1->id, $team2->id, $team3->id]);

        $match1 = TheMatch::factory()
            ->for($tournament)
            ->for($team1, 'homeTeam')
            ->for($team2, 'awayTeam')
            ->create(['is_played' => true]);
        $match2 = TheMatch::factory()
            ->for($tournament)
            ->for($team2, 'homeTeam')
            ->for($team1, 'awayTeam')
            ->create(['is_played' => false]);
        $match3 = TheMatch::factory()
            ->for($tournament)
            ->for($team2, 'homeTeam')
            ->for($team3, 'awayTeam')
            ->create(['is_played' => false]);

        $matches = $this->matchService->getRemainingMatches($tournament, $team1);

        $this->assertInstanceOf(Collection::class, $matches);
        $this->assertCount(1, $matches);
        $this->assertTrue($matches->contains($match2));
        $this->assertFalse($matches->contains($match1));
        $this->assertFalse($matches->contains($match3));
    }

    public function test_play_returns_false_if_match_is_already_played()
    {
        $match = Mockery::mock(TheMatch::class);
        $match->shouldReceive('getAttribute')->with('is_played')->andReturn(true);

        $result = $this->matchService->play($match, 2, 1);

        $this->assertFalse($result);
    }

    public function test_play_updates_match_and_calls_required_services()
    {
        $tournament = Mockery::mock(Tournament::class);
        $match = Mockery::mock(TheMatch::class);

        $match->shouldReceive('getAttribute')->with('tournament')->andReturn($tournament);
        $match->shouldReceive('getAttribute')->with('is_played')->andReturn(false);

        $match->shouldReceive('setAttribute')->with('home_score', 3);
        $match->shouldReceive('setAttribute')->with('away_score', 1);
        $match->shouldReceive('setAttribute')->with('is_played', true);

        $match->shouldReceive('save')->once()->andReturn(true);

        $this->tournamentService->shouldReceive('updateTournamentByPlayedMatch')
            ->once()
            ->with($tournament, $match);

        $this->standingService->shouldReceive('updateMatchStandingsByPlayedMatch')
            ->once()
            ->with($match);

        $result = $this->matchService->play($match, 3, 1);

        $this->assertTrue($result);
    }
}
