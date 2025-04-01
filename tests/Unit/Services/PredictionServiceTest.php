<?php

namespace Tests\Unit\Services;

use App\Models\Team;
use App\Models\Tournament;
use App\Services\PredictionService;
use App\Services\TheMatchService;
use App\Enums\TournamentStatus;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Tests\TestCase;

class PredictionServiceTest extends TestCase
{
    protected TheMatchService $matchService;
    protected PredictionService $predictionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->matchService = Mockery::mock(TheMatchService::class);
        $this->predictionService = new PredictionService($this->matchService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_predict_championship_rates_returns_empty_array_when_tournament_is_not_started()
    {
        $tournament = Mockery::mock(Tournament::class);
        $tournament->shouldReceive('getAttribute')
            ->with('status')
            ->andReturn(TournamentStatus::NotStarted);

        $result = $this->predictionService->predictChampionshipRates($tournament);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_predict_championship_rates_returns_empty_array_when_tournament_is_finished()
    {
        $tournament = Mockery::mock(Tournament::class);
        $tournament->shouldReceive('getAttribute')
            ->with('status')
            ->andReturn(TournamentStatus::Finished);

        $result = $this->predictionService->predictChampionshipRates($tournament);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_predict_championship_rates_returns_empty_array_when_not_enough_weeks_played()
    {
        $tournament = Mockery::mock(Tournament::class);
        $tournament->shouldReceive('getAttribute')
            ->with('status')
            ->andReturn(TournamentStatus::InProgress);
        $tournament->shouldReceive('getAttribute')
            ->with('last_played_week')
            ->andReturn(PredictionService::MINIMUM_PLAYED_WEEKS_TO_PREDICT - 1);

        $result = $this->predictionService->predictChampionshipRates($tournament);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_predict_championship_rates_returns_empty_array_when_no_teams()
    {
        $tournament = Mockery::mock(Tournament::class);
        $tournament->shouldReceive('getAttribute')
            ->with('status')
            ->andReturn(TournamentStatus::InProgress);
        $tournament->shouldReceive('getAttribute')
            ->with('last_played_week')
            ->andReturn(PredictionService::MINIMUM_PLAYED_WEEKS_TO_PREDICT);
        $tournament->shouldReceive('getAttribute')->with('teams')->andReturn(new Collection());

        $result = $this->predictionService->predictChampionshipRates($tournament);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_is_championship_mathematically_possible()
    {
        $tournament = Mockery::mock(Tournament::class);
        $team = Mockery::mock(Team::class);
        $team->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(1);

        $tournament->shouldReceive('getAttribute')
            ->with('standings')
            ->andReturn(new Collection([
                (object)['team_id' => 1, 'points' => 6]
            ]));

        $remainingMatches = new Collection([1, 2]);

        $this->matchService->shouldReceive('getRemainingMatches')
            ->with($tournament, $team)
            ->andReturn($remainingMatches);

        $leaderPoints = 10;

        $method = new \ReflectionMethod(PredictionService::class, 'isChampionshipMathematicallyPossible');
        $method->setAccessible(true);

        $result = $method->invoke($this->predictionService, $tournament, $team, $leaderPoints);

        // 6 + (2 * 3) = 12 > 10
        $this->assertTrue($result);
    }

    public function test_is_championship_mathematically_possible_returns_false_when_not_possible()
    {
        $tournament = Mockery::mock(Tournament::class);
        $team = Mockery::mock(Team::class);
        $team->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(1);

        $tournament->shouldReceive('getAttribute')
            ->with('standings')
            ->andReturn(new Collection([
                (object)['team_id' => 1, 'points' => 6]
            ]));

        $this->matchService->shouldReceive('getRemainingMatches')
            ->with($tournament, $team)
            ->andReturn(new Collection([1]));

        $leaderPoints = 10;

        $method = new \ReflectionMethod(PredictionService::class, 'isChampionshipMathematicallyPossible');
        $method->setAccessible(true);

        $result = $method->invoke($this->predictionService, $tournament, $team, $leaderPoints);

        // 6 + (1 * 3) = 9 < 10
        $this->assertFalse($result);
    }

    public function test_calculate_team_strength_returns_teams_strength_when_no_standing()
    {
        $tournament = Mockery::mock(Tournament::class);

        $team = Mockery::mock(Team::class);
        $team->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(1);
        $team->shouldReceive('getAttribute')
            ->with('strength')
            ->andReturn(75);

        $tournament->shouldReceive('getAttribute')
            ->with('standings')
            ->andReturn(new Collection([]));

        $method = new \ReflectionMethod(PredictionService::class, 'calculateTeamStrength');
        $method->setAccessible(true);

        $result = $method->invoke($this->predictionService, $tournament, $team);

        $this->assertIsFloat($result);
        $this->assertEquals(75.0, $result);
    }

    public function test_calculate_team_strength()
    {
        $team = Mockery::mock(Team::class);
        $team->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(1);
        $team->shouldReceive('getAttribute')
            ->with('strength')
            ->andReturn(75);

        $tournament = Mockery::mock(Tournament::class);
        $tournament->shouldReceive('getAttribute')
            ->with('standings')
            ->andReturn(new Collection([
                (object)[
                    'team_id' => 1,
                    'played' => 4,
                    'won' => 4,
                    'drawn' => 0,
                    'lost' => 1,
                    'goal_difference' => 10,
                    'points' => 12,
                ],
            ]));
        $tournament->shouldReceive('getAttribute')
            ->with('number_of_weeks')
            ->andReturn(6);

        $method = new \ReflectionMethod(PredictionService::class, 'calculateTeamStrength');
        $method->setAccessible(true);

        $result = $method->invoke($this->predictionService, $tournament, $team);

        $this->assertIsFloat($result);
        $this->assertEquals(81.66666666666667, $result);
    }


    public function test_adjust_for_remaining_fixtures_returns_given_strength_when_no_remaining_matches()
    {
        $team = Mockery::mock(Team::class);
        $team->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(1);

        $tournament = Mockery::mock(Tournament::class);
        $tournament->shouldReceive('getAttribute')
            ->with('number_of_weeks')
            ->andReturn(6);

        $this->matchService->shouldReceive('getRemainingMatches')
            ->with($tournament, $team)
            ->andReturn(new Collection([]));

        $method = new \ReflectionMethod(PredictionService::class, 'adjustForRemainingFixtures');
        $method->setAccessible(true);

        $result = $method->invoke($this->predictionService, 75.0, $team, $tournament);

        $this->assertIsFloat($result);
        $this->assertEquals(75.0, $result);
    }

    public function test_adjust_for_remaining_fixtures()
    {
        $team = Mockery::mock(Team::class);
        $team->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(1);

        $tournament = Mockery::mock(Tournament::class);
        $tournament->shouldReceive('getAttribute')
            ->with('number_of_weeks')
            ->andReturn(6);

        $tournament->shouldReceive('getAttribute')
            ->with('teams')
            ->andReturn(new Collection([
                (object)[
                    'id' => 1,
                    'strength' => 75,
                ],
                (object)[
                    'id' => 2,
                    'strength' => 100,
                ],
            ]));

        $remainingMatches = new Collection([
            (object) [
                'home_team_id' => 2,
                'away_team_id' => 1,
            ],
        ]);

        $this->matchService->shouldReceive('getRemainingMatches')
            ->with($tournament, $team)
            ->andReturn($remainingMatches);

        $method = new \ReflectionMethod(PredictionService::class, 'adjustForRemainingFixtures');
        $method->setAccessible(true);

        $result = $method->invoke($this->predictionService, 75.0, $team, $tournament);

        $this->assertIsFloat($result);
        $this->assertEquals(73.75, $result);
    }

    public function test_predict_championship_rates_returns_correct_predictions()
    {
        $teams = collect([
            (object)['id' => 1],
            (object)['id' => 2],
        ]);

        $method = new \ReflectionMethod(PredictionService::class, 'calculatePredictionsOfChampionship');
        $method->setAccessible(true);

        $result = $method->invoke($this->predictionService, $teams, [1 => 75.0, 2 => 100.0]);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals(100.0, $result[0]['strength_rating']);
        $this->assertEquals(75.0, $result[1]['strength_rating']);
    }
}
