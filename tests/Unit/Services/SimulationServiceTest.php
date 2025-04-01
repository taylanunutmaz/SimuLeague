<?php

namespace Tests\Unit\Services;

use App\Models\TheMatch;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\Standing;
use App\Services\SimulationService;
use App\Services\TheMatchService;
use App\Enums\TournamentStatus;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Tests\TestCase;

class SimulationServiceTest extends TestCase
{
    protected LegacyMockInterface|MockInterface|TheMatchService $matchService;
    protected SimulationService $simulationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->matchService = Mockery::mock(TheMatchService::class);
        $this->simulationService = new SimulationService($this->matchService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_play_all_weeks()
    {
        $tournament = Mockery::mock(Tournament::class);
        $tournament->shouldReceive('getAttribute')
            ->with('status')
            ->andReturn(TournamentStatus::InProgress);
        $tournament->shouldReceive('getAttribute')
            ->with('number_of_weeks')
            ->andReturn(6);
        $tournament->shouldReceive('getAttribute')
            ->with('last_played_week')
            ->andReturn(3);

        $simulationService = Mockery::mock(SimulationService::class, [$this->matchService])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $simulationService->shouldReceive('playNextWeek')
            ->with($tournament)
            ->times(3);

        $simulationService->playAllWeeks($tournament);

        $this->assertTrue(true);
    }

    public function test_play_next_week()
    {
        $tournament = Mockery::mock(Tournament::class);
        $tournament->shouldReceive('getAttribute')
            ->with('status')
            ->andReturn(TournamentStatus::InProgress);

        $tournament->shouldReceive('getAttribute')
            ->with('last_played_week')
            ->andReturn(2);

        $tournament->shouldReceive('fresh')
            ->andReturnSelf();

        $matchesQuery = Mockery::mock('Illuminate\Database\Eloquent\Builder');
        $tournament->shouldReceive('matches')
            ->andReturn($matchesQuery);

        $matchesQuery->shouldReceive('where')
            ->with('is_played', false)
            ->andReturnSelf();

        $matchesQuery->shouldReceive('where')
            ->with('week', 3)
            ->andReturnSelf();

        $match1 = Mockery::mock(TheMatch::class);
        $match2 = Mockery::mock(TheMatch::class);
        $matches = new Collection([$match1, $match2]);
        $matchesQuery->shouldReceive('get')
            ->andReturn($matches);

        $simulationService = Mockery::mock(SimulationService::class, [$this->matchService])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $simulationService->shouldReceive('simulateMatch')
            ->with($match1)
            ->once();

        $simulationService->shouldReceive('simulateMatch')
            ->with($match2)
            ->once();

        $simulationService->playNextWeek($tournament);

        $this->assertTrue(true);
    }

    public function test_reset_data()
    {
        $match1 = Mockery::mock(TheMatch::class);
        $match1->shouldReceive('setAttribute')->with('home_score', null);
        $match1->shouldReceive('setAttribute')->with('away_score', null);
        $match1->shouldReceive('setAttribute')->with('is_played', false);
        $match1->shouldReceive('save')->andReturn(true);

        $standing1 = Mockery::mock(Standing::class);
        $standing1->shouldReceive('setAttribute')->with('played', 0);
        $standing1->shouldReceive('setAttribute')->with('won', 0);
        $standing1->shouldReceive('setAttribute')->with('drawn', 0);
        $standing1->shouldReceive('setAttribute')->with('lost', 0);
        $standing1->shouldReceive('setAttribute')->with('goals_for', 0);
        $standing1->shouldReceive('setAttribute')->with('goal_difference', 0);
        $standing1->shouldReceive('setAttribute')->with('goals_against', 0);
        $standing1->shouldReceive('setAttribute')->with('points', 0);
        $standing1->shouldReceive('save')->andReturn(true);

        $tournament = Mockery::mock(Tournament::class);
        $tournament->shouldReceive('getAttribute')
            ->with('matches')
            ->andReturn(new Collection([$match1]));
        $tournament->shouldReceive('getAttribute')
            ->with('standings')
            ->andReturn(new Collection([$standing1]));

        $tournament->shouldReceive('setAttribute')
            ->with('last_played_week', 0);
        $tournament->shouldReceive('setAttribute')
            ->with('status', TournamentStatus::InProgress);
        $tournament->shouldReceive('save')
            ->andReturn(true);

        $result = $this->simulationService->resetData($tournament);

        $this->assertTrue($result);
    }

    public function test_ensure_tournament_playable_throws_exception()
    {
        $tournament = Mockery::mock(Tournament::class);
        $tournament->shouldReceive('getAttribute')
            ->with('status')
            ->andReturn(TournamentStatus::Finished);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Tournament is not playable');

        $this->simulationService->playNextWeek($tournament);
    }

    public function test_simulate_match()
    {
        $homeTeam = Mockery::mock(Team::class);
        $homeTeam->shouldReceive('getAttribute')
            ->with('strength')
            ->andReturn(75.0);

        $awayTeam = Mockery::mock(Team::class);
        $awayTeam->shouldReceive('getAttribute')
            ->with('strength')
            ->andReturn(100.0);

        $match = Mockery::mock(TheMatch::class);
        $match->shouldReceive('getAttribute')
            ->with('homeTeam')
            ->andReturn($homeTeam);
        $match->shouldReceive('getAttribute')
            ->with('awayTeam')
            ->andReturn($awayTeam);

        $this->matchService->shouldReceive('play')
            ->with($match, Mockery::any(), Mockery::any())
            ->andReturn(true);

        $method = new \ReflectionMethod(SimulationService::class, 'simulateMatch');
        $method->setAccessible(true);

        $result = $method->invoke($this->simulationService, $match);

        $this->assertTrue($result);
    }

    public function test_calculate_goals()
    {
        $method = new \ReflectionMethod(SimulationService::class, 'calculateGoals');
        $method->setAccessible(true);

        $strength = 50.0;
        $result = $method->invoke($this->simulationService, $strength);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }
}
