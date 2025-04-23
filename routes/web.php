<?php

use App\Http\Controllers\ActivationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GamesController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\PresencesController;
use App\Http\Controllers\RankingsController;
use App\Http\Controllers\RoundsController;
use App\Http\Controllers\SettingsController;
use App\Models\Game;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::controller(PagesController::class)->group(function (): void {
    Route::get('/', 'index');
    Route::get('/home', 'index');
    Route::get('/about', 'about');
});
Auth::routes();

Route::controller(ActivationController::class)->group(function (): void {
    Route::get('/password', 'index')->name('activation');
    Route::post('/activation', 'send')->name('sendActivation');
    Route::get('/activation/{activate}/{email}', 'activate');
    Route::post('/activation_manually', 'activate_man')->name('postActivation');
});

// User Group

Route::resource('presences', PresencesController::class)->middleware('auth');
Route::resource('rounds', RoundsController::class)->middleware('auth');

Route::get('/rankings/{userId}', [RankingsController::class, 'getDetails'])->middleware(['auth', 'inn']);
Route::resource('rankings', RankingsController::class)->middleware(['auth', 'inn']);
Route::resource('games', GamesController::class)->middleware('auth');
Route::post('/presences/{id}/edit', [PresencesController::class, 'update'])->name('updatePresence')->middleware('auth');

Route::controller(SettingsController::class)->group(function (): void {
    Route::get('settings', 'index')->middleware('auth');
    Route::post('settings', 'update')->name('settings.update')->middleware('auth');
    Route::post('/changePassword', 'ChangePassword')->name('changePassword')->middleware('auth');
    Route::post('/changeEmail', 'ChangeEmail')->name('changeEmail')->middleware('auth');
});
Route::controller(NotificationsController::class)->group(function (): void {
    Route::get('notifications', 'read')->name('readNotifications')->middleware('auth');
});

// End User Group

// Admin Group
Route::controller(AdminController::class)->group(function (): void {
    Route::get('/Admin', 'admin')->name('Admin')->middleware('auth');
    Route::get('/Admin/Rounds', 'adminRounds')->middleware('inn');
    Route::get('/Admin/Rounds/create', 'RoundsCreate');
    Route::get('/Admin/Presences', 'adminPresences')->middleware('inn');
    Route::get('/Admin/Presences/create', 'InitPresences');
    Route::get('/Admin/Rankings', 'adminRankings')->middleware('inn');
    Route::get('/Admin/RankingList', 'RankingList');
    Route::get('/Admin/RankingList/{Round}/calculate', 'InitCalculation');
    Route::get('/Admin/RankingList/create', 'InitRanking');
    Route::get('/Admin/RatingList', 'RatingList');
    Route::get('/Admin/Reset', 'ResetSeason');
    Route::get('/Admin/Match/{Round}', 'FillArrayPlayers');
    Route::get('/Admin/Games', 'adminGames')->name('admin.games')->middleware('inn');
    Route::get('/Admin/{Game}/Games', 'game');
    Route::get('/Admin/Users', 'adminUsers')->middleware('inn');
    Route::get('/Admin/Game/Add/{Round}', 'AddGame');
    Route::get('/Admin/Presence/Add', 'AddPresence');
    Route::get('/Admin/RankingList/add', 'AddRanking');
    Route::get('/Admin/RankingList/back', 'BackRanking');
    Route::post('/Admin/RankingList/storeRanking', 'storeRanking');
    Route::get('/rounds/{Round}/rankings', 'PublishRanking');
    Route::get('/rounds/{Round}/games', 'PublishGames');
    Route::get('/recalculateTPR', 'RecalculateTPR');
    Route::get('/recalculateRatop', 'RecalculateRatop');
    Route::get('/Admin/RankingList/reset', 'ResetRanking');
    Route::get('/Admin/RankingList/{Ranking}', 'EditRanking');

    Route::post('/Admin/LoadRatings', 'loadRatings')->name('import_process');
    Route::post('/Admin/LoadRounds', 'loadRounds')->name('import_process_rounds');
    Route::post('/Admin/Rounds/create', 'RoundStore')->name('RoundStore');
    Route::post('/Admin/Games/{game}/update', 'UpdateGame')->name('UpdateGame');
    Route::post('/Admin/Users/update', 'UpdateUser')->name('UpdateUser');
    Route::post('/Admin/Config', 'Instellingen')->name('config')->middleware('inn');
    Route::get('/Admin/Config', 'adminConfigs')->middleware('inn');
    Route::post('/Admin/Game/create', 'storeGame')->name('storeGame');
    Route::post('/Admin/Presence/create', 'storePresence')->name('storePresence');
    Route::post('/Admin/Ranking/create', 'storeRanking')->name('storeRanking');
    Route::post('/Admin/RankingList/StoreUpdatedRanking', 'StoreUpdatedRanking')->name('StoreUpdatedRanking');

    // Administrator-pages (deletes)
    Route::delete('/Admin/{Presence}/Presences', 'DestroyPresences')->name('destroyPresences')->middleware('inn');
    Route::delete('/Admin/{User}/User', 'DestroyUser')->name('destroyUser');
    Route::delete('/Admin/{Game}/Games', 'DestroyGames')->name('destroyGames');
    Route::delete('/Admin/{Round}/Rounds', 'DestroyRounds')->name('destroyRounds');

    Route::get('/Admin/fixRounds', function(){
        $rankings = \App\Models\Ranking::all();
        foreach($rankings as $rank)
        {
            $user_id = $rank->user_id;
            $gamesWhite = Game::where('white', $user_id)->where('black', '<>', 'Other')->count();
            $gamesBlack = Game::where('black', $user_id)->count();
            $totalGames = $gamesWhite + $gamesBlack;
            $rank->amount = $totalGames;
            $rank->save();
        }
        return "Recounted the games";
    });
})->middleware('admin');

// Web-Push #
Route::post('/push', 'PushController@store');
Route::get('/push', 'PushController@push')->name('push');

// RSS Feed #
Route::get('/feed/{API_Token}', 'iOSNotificationsController@getFeedItems');

Route::get('/sendNotification', 'AdminController@SendNotification')->middleware('admin');
