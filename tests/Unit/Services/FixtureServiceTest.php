<?php

namespace Tests\Unit\Services;

use App\Models\TheMatch;
use App\Models\Tournament;
use App\Models\Team;
use App\Services\FixtureService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class FixtureServiceTest extends TestCase
{
    use RefreshDatabase;

    private FixtureService $fixtureService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtureService = new FixtureService();
    }

    public function test_generate_fixtures_with_even_number_of_teams()
    {
        $tournament = Tournament::factory()->create();

        $teams = Team::factory(4)->create();
        $tournament->teams()->attach($teams->pluck('id'));

        $result = $this->fixtureService->generateFixtures($tournament);

        $this->assertTrue($result);
        $this->assertEquals(6, $tournament->number_of_weeks);

        $matchCount = TheMatch::where('tournament_id', $tournament->id)->count();
        $this->assertEquals(12, $matchCount);

        foreach ($teams as $homeTeam) {
            foreach ($teams as $awayTeam) {
                if ($homeTeam->id !== $awayTeam->id) {
                    $this->assertEquals(
                        1,
                        TheMatch::where([
                            'tournament_id' => $tournament->id,
                            'home_team_id' => $homeTeam->id,
                            'away_team_id' => $awayTeam->id,
                        ])->count(),
                        "Team {$homeTeam->id} should play at home against team {$awayTeam->id} once"
                    );
                }
            }
        }
    }

    public function test_generate_fixtures_with_odd_number_of_teams()
    {
        $tournament = Tournament::factory()->create();

        $teams = Team::factory(3)->create();
        $tournament->teams()->attach($teams->pluck('id'));

        $result = $this->fixtureService->generateFixtures($tournament);

        $this->assertTrue($result);
        $this->assertEquals(6, $tournament->number_of_weeks);

        $matchCount = TheMatch::where('tournament_id', $tournament->id)->count();
        $this->assertEquals(6, $matchCount);

        foreach ($teams as $homeTeam) {
            foreach ($teams as $awayTeam) {
                if ($homeTeam->id !== $awayTeam->id) {
                    $this->assertEquals(
                        1,
                        TheMatch::where([
                            'tournament_id' => $tournament->id,
                            'home_team_id' => $homeTeam->id,
                            'away_team_id' => $awayTeam->id,
                        ])->count(),
                        "Team {$homeTeam->id} should play at home against team {$awayTeam->id} once"
                    );
                }
            }
        }
    }

    public function test_generate_fixtures_with_less_than_two_teams_throws_exception()
    {
        $tournament = Tournament::factory()->create();
        $team = Team::factory()->create();
        $tournament->teams()->attach($team->id);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Tournament must have at least 2 teams.');

        $this->fixtureService->generateFixtures($tournament);
    }
}
