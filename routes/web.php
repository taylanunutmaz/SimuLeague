<?php

use App\Http\Controllers\SimulationController;
use App\Http\Controllers\TournamentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

Route::resource('tournaments', TournamentController::class)
    ->only(['index', 'show', 'create', 'store', 'destroy']);

Route::get(
    'tournaments/{tournament}/fixtures',
    [TournamentController::class, 'fixtures']
)->name('tournaments.fixtures');

Route::post(
    'tournaments/{tournament}/generate-fixtures',
    [TournamentController::class, 'generateFixtures']
)->name('tournaments.fixtures.generate');

Route::get('tournaments/{tournament}/simulation',
    [SimulationController::class, 'index']
)->name('tournaments.simulation.index');

Route::post('tournaments/{tournament}/start-simulation',
    [SimulationController::class, 'start']
)->name('tournaments.simulation.start');

Route::post('tournaments/{tournament}/play-all-weeks',
    [SimulationController::class, 'playAllWeeks']
)->name('tournaments.simulation.play-all-weeks');

Route::post('tournaments/{tournament}/play-next-week',
    [SimulationController::class, 'playNextWeek']
)->name('tournaments.simulation.play-next-week');

Route::post('tournaments/{tournament}/reset-simulation',
    [SimulationController::class, 'reset']
)->name('tournaments.simulation.reset');
