<?php

namespace Tests\Unit\Services;

use App\Models\Standing;
use App\Models\Team;
use App\Models\TheMatch;
use App\Models\Tournament;
use App\Services\StandingService;
use App\Enums\TournamentStatus;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Tests\TestCase;

class StandingServiceTest extends TestCase
{
    protected StandingService $standingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->standingService = new StandingService();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_standings()
    {
        $teams = Team::factory()->count(2)->create();

        $tournament = Tournament::factory()->create();

        $tournament->teams()->attach($teams);

        TheMatch::factory()->create([
            'tournament_id' => $tournament->id,
            'home_team_id' => $teams[0]->id,
            'away_team_id' => $teams[1]->id,
            'week' => 1
        ]);

        $this->standingService->createStandings($tournament);

        $this->assertEquals(2, $tournament->standings()->count());
        foreach ($teams as $team) {
            $this->assertDatabaseHas('standings', [
                'tournament_id' => $tournament->id,
                'team_id' => $team->id
            ]);
        }

        $this->assertEquals(TournamentStatus::InProgress, $tournament->fresh()->status);
    }

    public function test_create_standings_with_less_than_two_teams_throws_exception()
    {
        $teams = new Collection([Mockery::mock(Team::class)]);

        $tournament = Mockery::mock(Tournament::class);
        $tournament->shouldReceive('teams')->andReturn($teams);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Tournament must have at least 2 teams.');

        $this->standingService->createStandings($tournament);
    }

    public function test_create_standings_without_matches_throws_exception()
    {
        $team1 = Mockery::mock(Team::class);
        $team2 = Mockery::mock(Team::class);
        $teams = new Collection([$team1, $team2]);

        $matches = new Collection([]);

        $tournament = Mockery::mock(Tournament::class);
        $tournament->shouldReceive('teams')->andReturn($teams);
        $tournament->shouldReceive('matches')->andReturn($matches);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Tournament fixtures are not generated.');

        $this->standingService->createStandings($tournament);
    }

    public function test_update_match_standings_by_played_match()
    {
        $homeStanding = Mockery::mock(Standing::class);
        $awayStanding = Mockery::mock(Standing::class);
        $match = Mockery::mock(TheMatch::class);
        $match->shouldReceive('getAttribute')->with('homeStanding')->andReturn($homeStanding);
        $match->shouldReceive('getAttribute')->with('awayStanding')->andReturn($awayStanding);
        $match->shouldReceive('getAttribute')->with('home_score')->andReturn(2);
        $match->shouldReceive('getAttribute')->with('away_score')->andReturn(1);

        $homeStanding->shouldReceive('getAttribute')->with('won')->andReturn(0);
        $homeStanding->shouldReceive('getAttribute')->with('drawn')->andReturn(0);
        $homeStanding->shouldReceive('getAttribute')->with('lost')->andReturn(0);
        $homeStanding->shouldReceive('getAttribute')->with('points')->andReturn(0);
        $homeStanding->shouldReceive('getAttribute')->with('played')->andReturn(0);
        $homeStanding->shouldReceive('getAttribute')->with('goals_for')->andReturn(0);
        $homeStanding->shouldReceive('getAttribute')->with('goals_against')->andReturn(0);


        $awayStanding->shouldReceive('getAttribute')->with('won')->andReturn(0);
        $awayStanding->shouldReceive('getAttribute')->with('drawn')->andReturn(0);
        $awayStanding->shouldReceive('getAttribute')->with('lost')->andReturn(0);
        $awayStanding->shouldReceive('getAttribute')->with('points')->andReturn(0);
        $awayStanding->shouldReceive('getAttribute')->with('played')->andReturn(0);
        $awayStanding->shouldReceive('getAttribute')->with('goals_for')->andReturn(0);
        $awayStanding->shouldReceive('getAttribute')->with('goals_against')->andReturn(0);

        $homeStanding->shouldReceive('setAttribute')->with('won', 1);
        $homeStanding->shouldReceive('setAttribute')->with('drawn', 0);
        $homeStanding->shouldReceive('setAttribute')->with('lost', 0);
        $homeStanding->shouldReceive('setAttribute')->with('points', 3);
        $homeStanding->shouldReceive('setAttribute')->with('played', 1);
        $homeStanding->shouldReceive('setAttribute')->with('goals_for', 2);
        $homeStanding->shouldReceive('setAttribute')->with('goals_against', 1);
        $homeStanding->shouldReceive('setAttribute')->with('goal_difference', 1);

        $awayStanding->shouldReceive('setAttribute')->with('won', 0);
        $awayStanding->shouldReceive('setAttribute')->with('drawn', 0);
        $awayStanding->shouldReceive('setAttribute')->with('lost', 1);
        $awayStanding->shouldReceive('setAttribute')->with('points', 0);
        $awayStanding->shouldReceive('setAttribute')->with('played', 1);
        $awayStanding->shouldReceive('setAttribute')->with('goals_for', 1);
        $awayStanding->shouldReceive('setAttribute')->with('goals_against', 2);
        $awayStanding->shouldReceive('setAttribute')->with('goal_difference', -1);

        $homeStanding->shouldReceive('save')->once();
        $awayStanding->shouldReceive('save')->once();

        $this->standingService->updateMatchStandingsByPlayedMatch($match);

        $this->assertTrue(true);
    }

    public function test_update_match_standings_throws_exception_when_standing_not_found()
    {
        $match = Mockery::mock(TheMatch::class);
        $match->shouldReceive('getAttribute')->with('homeStanding')->andReturn(null);
        $match->shouldReceive('getAttribute')->with('awayStanding')->andReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Standings not found for the match.');

        $this->standingService->updateMatchStandingsByPlayedMatch($match);
    }
}
