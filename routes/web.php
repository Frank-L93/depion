<?php

use App\Http\Controllers\ActivationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GamesController;
use App\Http\Controllers\PresencesController;
use App\Http\Controllers\RankingsController;
use App\Http\Controllers\RoundsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\NotificationsController;
use Illuminate\Support\Facades\Auth;

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

Route::controller(PagesController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('/home', 'index');
    Route::get('/about', 'about');
});
Auth::routes();

Route::controller(ActivationController::class)->group(function () {
    Route::get('/password', 'index')->name('activation');
    Route::post('/activation', 'send')->name('sendActivation');
    Route::get('/activation/{activate}/{email}', 'activate');
    Route::post('/activation_manually', 'activate_man')->name('postActivation');
});


# User Group

Route::resource('presences', PresencesController::class)->middleware('auth');
Route::resource('rounds', RoundsController::class)->middleware('auth');
Route::resource('rankings', RankingsController::class)->middleware('auth');
Route::resource('games', GamesController::class)->middleware('auth');
Route::post('/presences/{id}/edit', [PresencesController::class, 'update'])->name('updatePresence')->middleware('auth');

Route::controller(SettingsController::class)->group(function () {
    Route::get('settings', 'index')->middleware('auth');
    Route::patch('settings', 'update')->name('settings.update')->middleware('auth');
    Route::post('/changePassword', 'ChangePassword')->name('changePassword')->middleware('auth');
    Route::post('/changeEmail', 'ChangeEmail')->name('changeEmail')->middleware('auth');
});
Route::controller(NotificationsController::class)->group(function(){
    Route::get('notifications', 'read')->name('readNotifications')->middleware('auth');
});

# End User Group

# Admin Group
Route::controller(AdminController::class)->group(function () {
    Route::get('/Admin', 'admin')->name('Admin')->middleware('auth');
    Route::get('/Admin/Rounds', 'RoundsIndex');
    Route::get('/Admin/Rounds/create', 'RoundsCreate');
    Route::get('/Admin/Presences', 'presences');
    Route::get('/Admin/Presences/create', 'InitPresences');
    Route::get('/Admin/RankingList', 'RankingList');
    Route::get('/Admin/RankingList/{Round}/calculate', 'InitCalculation');
    Route::get('/Admin/RankingList/create', 'InitRanking');
    Route::get('/Admin/RatingList', 'RatingList');
    Route::get('/Admin/Reset', 'ResetSeason');
    Route::get('/Admin/Match/{Round}', 'FillArrayPlayers');
    Route::get('/Admin/Games', 'games');
    Route::get('/Admin/{Game}/Games', 'game');
    Route::get('/Admin/users/list', 'List');
    Route::get('/Admin/users/list2', 'List2');
    Route::get('/Admin/Game/Add/{Round}', 'AddGame');
    Route::get('/Admin/Presence/Add', 'AddPresence');
    Route::get('/Admin/RankingList/add', 'AddRanking');
    Route::post('/Admin/RankingList/storeRanking', 'storeRanking');
    Route::get('/rounds/{Round}/rankings', 'PublishRanking');
    Route::get('/rounds/{Round}/games', 'PublishGames');
    Route::get('/recalculateTPR', 'RecalculateTPR');


    Route::post('/Admin/LoadRatings', 'loadRatings')->name('import_process');
    Route::post('/Admin/LoadRounds', 'loadRounds')->name('import_process_rounds');
    Route::post('/Admin/Rounds/create', 'RoundStore')->name('RoundStore');
    Route::post('/Admin/Games/update', 'UpdateGame')->name('UpdateGame');
    Route::post('/Admin/Users/update', 'UpdateUser')->name('ApdateUser');
    Route::post('/Admin/Config', 'Instellingen')->name('config');
    Route::post('/Admin/Game/create', 'storeGame')->name('storeGame');
    Route::post('/Admin/Presence/create', 'storePresence')->name('storePresence');
    Route::post('/Admin/Ranking/create', 'storeRanking')->name('storeRanking');

    // Administrator-pages (deletes)
    Route::delete('/Admin/{Presence}/Presences', 'DestroyPresences')->name('destroyPresences');
    Route::delete('/Admin/{User}/User', 'DestroyUser')->name('destroyUser');
    Route::delete('/Admin/{Game}/Games', 'DestroyGames')->name('destroyGames');
    Route::delete('/Admin/{Round}/Rounds', 'DestroyRounds')->name('destroyRounds');
})->middleware('admin');

# Web-Push #
Route::post('/push', 'PushController@store');
Route::get('/push', 'PushController@push')->name('push');

# RSS Feed #
Route::get('/feed/{API_Token}', 'iOSNotificationsController@getFeedItems');

Route::get('/sendNotification', 'AdminController@SendNotification')->middleware('admin');
