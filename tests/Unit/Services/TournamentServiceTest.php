<?php

namespace Tests\Unit\Services;

use App\Models\TheMatch;
use App\Models\Tournament;
use App\Services\TournamentService;
use App\Enums\TournamentStatus;
use PHPUnit\Framework\TestCase;
use Mockery;

class TournamentServiceTest extends TestCase
{
    protected TournamentService $tournamentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tournamentService = new TournamentService();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_update_tournament_by_played_match_returns_false_when_match_is_not_played()
    {
        $tournament = Mockery::mock(Tournament::class);
        $match = Mockery::mock(TheMatch::class);
        $match->shouldReceive('getAttribute')->with('is_played')->andReturn(false);

        $result = $this->tournamentService->updateTournamentByPlayedMatch($tournament, $match);

        $this->assertFalse($result);
    }

    public function test_update_tournament_by_played_match_updates_last_played_week()
    {
        $tournament = Mockery::mock(Tournament::class);
        $tournament->shouldReceive('getAttribute')->with('last_played_week')->andReturn(3);
        $tournament->shouldReceive('getAttribute')->with('number_of_weeks')->andReturn(6);
        $tournament->shouldReceive('setAttribute')->with('last_played_week', 5);

        $match = Mockery::mock(TheMatch::class);
        $match->shouldReceive('getAttribute')->with('is_played')->andReturn(true);
        $match->shouldReceive('getAttribute')->with('week')->andReturn(5);

        $tournament->shouldReceive('save')->once()->andReturn(true);

        $result = $this->tournamentService->updateTournamentByPlayedMatch($tournament, $match);

        $this->assertTrue($result);
    }

    public function test_update_tournament_by_played_match_sets_tournament_status_to_finished_when_all_weeks_played()
    {
        $tournament = Mockery::mock(Tournament::class)->makePartial();
        $tournament->shouldReceive('getAttribute')->with('last_played_week')->andReturn(3);
        $tournament->shouldReceive('getAttribute')->with('number_of_weeks')->andReturn(6);
        $tournament->shouldReceive('getAttribute')->with('status')->andReturn(TournamentStatus::InProgress);
        $tournament->shouldReceive('setAttribute')->with('status', TournamentStatus::Finished);

        $match = Mockery::mock(TheMatch::class);
        $match->shouldReceive('getAttribute')->with('is_played')->andReturn(true);
        $match->shouldReceive('getAttribute')->with('week')->andReturn(6);

        $tournament->shouldReceive('save')->once()->andReturn(true);

        $result = $this->tournamentService->updateTournamentByPlayedMatch($tournament, $match);

        $this->assertTrue($result);
    }

    public function test_update_tournament_by_played_match_keeps_higher_week_number()
    {
        $tournament = Mockery::mock(Tournament::class);
        $tournament->shouldReceive('getAttribute')->with('last_played_week')->andReturn(5);
        $tournament->shouldReceive('getAttribute')->with('number_of_weeks')->andReturn(6);
        $tournament->shouldReceive('setAttribute')->with('last_played_week', 5);

        $match = Mockery::mock(TheMatch::class);
        $match->shouldReceive('getAttribute')->with('is_played')->andReturn(true);
        $match->shouldReceive('getAttribute')->with('week')->andReturn(3);

        $tournament->shouldReceive('save')->once()->andReturn(true);

        $result = $this->tournamentService->updateTournamentByPlayedMatch($tournament, $match);

        $this->assertTrue($result);
    }
}
