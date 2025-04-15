<?php

namespace App\Http\Controllers;

use App\Actions\MatchGames;
use App\Actions\newCalculation;
use App\Jobs\ProcessCalculation;
use App\Jobs\ProcessMatching;
use App\Models\Config;
use App\Models\Game;
use App\Models\Presence;
use App\Models\Ranking;
use App\Models\Round;
use App\Models\User;
use App\Services\DetailsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

global $k;
class AdminController extends Controller
{
    public function adminGames()
    {
        $games = Game::all();
        $rounds = Round::orderBy('date')->get();
        $users = User::all();
        $round_to_process = Round::where('processed', null)->orWhere('processed', 0)->orderBy('date')->first();
        if ($round_to_process == null) {
            $round_to_process = new Round;
            $round_to_process->id = 0;
        }

        return Inertia::render('Admin/Games', ['games' => $games, 'rounds' => $rounds, 'users' => $users, 'round_to_process' => $round_to_process]);
    }

    public function adminUsers()
    {
        $users = User::all();

        return Inertia::render('Admin/Users', ['users' => $users]);
    }

    public function adminRounds()
    {
        $rounds = Round::orderBy('id')->get();

        return Inertia::render('Admin/Rounds', ['rounds' => $rounds]);
    }

    public function adminPresences(Request $request)
    {
        $search = $request->input('search');
        $query = Presence::with('user');

        if ($search) {
            $query->whereHas('user', function ($q) use ($search): void {
                $q->where('name', 'like', '%'.$search.'%');
            });
        }

        $presences = $query->paginate(50); // Adjust the number of items per page as needed

        return Inertia::render('Admin/Presences', ['presences' => $presences, 'search' => $search]);
    }

    public function adminRankings()
    {
        $rankings = Ranking::with('user')->orderBy('value', 'desc')->get();

        return Inertia::render('Admin/Rankings', ['ranking' => $rankings]);
    }

    public function adminConfigs()
    {
        $configs = Config::all();

        return Inertia::render('Admin/Configs', ['configs' => $configs]);
    }

    // Index page of our Admin
    public function admin()
    {
        $configs = Config::all();

        return view('admin.index')->with('configs', $configs);
    }

    // Round functionallity of our Admin
    public function RoundsCreate()
    {
        $round = Round::all();

        return view('rounds.create')->with('rounds', $round);
    }

    public function RoundStore(Request $request)
    {
        $this->validate($request, [
            'round' => 'required',
            'date' => 'required',
        ]);

        $round = new Round;
        $round->round = $request->input('round');
        $round->uuid = base64_encode(rand(0, 999999));
        $round_exist = Round::where('round', $round->round)->get();
        if ($round_exist->isEmpty()) {
            $round->date = $request->input('date');
            $round->save();
        } else {
            return redirect('/Admin')->with('error', 'Deze ronde is al aangemaakt!');
        }

        return redirect('/Admin')->with('success', 'Ronde aangemaakt!');
    }

    // Presences functionallity of our Admin

    public function DestroyPresences($id)
    {

        if (Gate::allows('admin', Auth::user())) {
            $presence = Presence::find($id);
            // Check if player has a game in this round

            $games_white = Game::where('round_id', $presence->round)->where('white', $presence->user_id)->get();
            $games_black = Game::where('round_id', $presence->round)->where('black', $presence->user_id)->get();
            if ($games_white->isEmpty() && $games_black->isEmpty()) {
                $presence->delete();

                return redirect('/Admin', 303)->with('success', 'Aanwezigheid verwijderd!');
            } elseif ($games_black->isEmpty()) {
                foreach ($games_white as $game) {
                    if ($game->black == 'Bye') {
                        $round = Round::find($game->round_id);
                        if ($round->processed == 1) {
                            return redirect('/Admin', 303)->with('error', 'Deze aanwezigheid kan niet meer verwijderd worden.');
                        }
                        $presence->delete();

                        return redirect('/Admin', 303)->with('success', 'Aanwezigheid verwijderd!');
                    } else {
                        return redirect('/Admin', 303)->with('error', 'Deze aanwezigheid kan niet meer verwijderd worden');
                    }
                }
            }

            return redirect('/Admin', 303)->with('error', 'Deze aanwezigheid kan niet meer verwijderd worden');
        } else {
            return redirect('/presences', 303)->with('error', 'Je hebt geen toegang tot administrator-paginas!');
        }
    }

    public function DestroyRounds($id)
    {
        if (Gate::allows('admin', Auth::user())) {
            $round = Round::find($id);
            $games = Game::where('round_id', $round->id)->get();
            foreach ($games as $game) {
                $game->delete();
            }
            $round->delete();

            return redirect('/Admin/Rounds', 303)->with('success', 'Ronde verwijderd en tevens partijen verwijderd uit die ronde!');
        } else {
            return redirect('/rounds', 303)->with('error', 'Je hebt geen toegang tot administrator-paginas!');
        }
    }

    // Games Functionallity of our Admin

    public function DestroyGames($id)
    {
        if (Gate::allows('admin', Auth::user())) {
            $game = Game::find($id);
            $game->delete();

            return redirect('/Admin', 303)->with('success', 'Partij verwijderd!');
        } else {
            return redirect('/games', 303)->with('error', 'Je hebt geen toegang tot administrator-paginas!');
        }
    }

    // Starting Matching process --> This you want somewehre else, but I dont know where. #help

    public function FillArrayPlayers($round) // loads all players that are needed to be paired in the specified round.
    {
        $players = [];
        $lower_value_set = 0;
        $presentPlayers = Presence::select('user_id')->where(['round' => $round, 'presence' => '1'])->get();
        foreach ($presentPlayers as $player) {
            $lowest_value_set = 0;
            array_push($players, $player->user_id);
            $lowest_value = Ranking::select('value')->orderBy('value', 'asc')->limit(1)->first();
            if ($lowest_value == null) {
                $lowest_value_set = Config::InitRanking('start');
            }
            $player_ranked = Ranking::where('user_id', $player->user_id)->get();
            // Player needs to be in Ranking, so add him if he does not appear there yet.
            if ($player_ranked->isEmpty()) {
                $ranking = new Ranking;
                $ranking->user_id = $player->user_id;
                $ranking->score = 0;
                // We will now give him a value for a player, with some sort of the same rating.

                $player_unranked = User::find($player->user_id);

                $player_closest_by = DB::table('users')->join('rankings', 'users.id', '=', 'rankings.user_id')->select('users.id', 'users.rating', 'rankings.value', DB::raw('ABS('.$player_unranked->rating.' - users.rating) as difference'))->orderby('difference')->limit(1)->first();

                if ($player_closest_by == null) {
                    // doe iets anders

                    if ($lowest_value_set == Config::InitRanking('start')) {
                        $ranking->value = $lowest_value_set;
                        $ranking->firstvalue = $lower_value_set;
                    } else {
                        $ranking->value = $lowest_value->value - 1;
                        $ranking->firstvalue = $lowest_value->value - 1;
                    }
                } else {
                    $ranking->value = $player_closest_by->value;
                    $ranking->firstvalue = $player_closest_by->value;
                }

                $ranking->save();
            }
        }

        // We now have all players, before matching, this needs to be sorted.
        return $this->FillArrayPlayersRanked($players, $round);
    }

    public function checkPaired($player, $round)
    {

        $paired_white = Game::where(['white' => $player, 'round_id' => $round])->get();
        $paired_black = Game::where(['black' => $player, 'round_id' => $round])->get();

        if ($paired_white->isEmpty() && $paired_black->isEmpty()) {
            return false;
        } else {
            return true;
        }
    }

    private function sort_value($a, $b)
    {
        return strnatcmp($b['value'], $a['value']);
    }

    public function FillArrayPlayersRanked($players, $round) // Fills an array of players to be paired together with their rank rank ? sorted ?
    {
        $playerswithranking = [];
        foreach ($players as $player) {
            $player = Ranking::where('user_id', $player)->first();
            $check_already_paired = $this->checkPaired($player->user_id, $round);
            // In weird cases players may already be paired (i.e. new pairings?) so check it.
            if (!$check_already_paired) {
                $player_array = ['id' => $player->user_id, 'rank' => $player->id, 'value' => $player->value];
                array_push($playerswithranking, $player_array);
            }
        }
        usort($playerswithranking, [$this, 'sort_value']); // Sorting on value.

        ProcessMatching::dispatch($playerswithranking, $round);

        // $matches = new MatchGames;
        // $matches->InitPairing($playerswithranking, $round); // Launch Pairing!
        return redirect('/Admin')->with('Success', 'Partijen worden aangemaakt!'); // Return will most likely not be called as in the pairing process, the last return that can be called is the return for the notifications which afterwards redirects to the Admin-page too. But for cases that this does not happen, this return is necessary.
    }

    // Helping function for UpdateGame, returns a json to fill editable list.
    public function List()
    {
        $users = User::all();
        $user_list = [];
        foreach ($users as $user) {
            array_push($user_list, ['value' => $user->id, 'text' => $user->name]);
        }

        return json_encode($user_list);
    }

    // Game Changing functionality of our Admin

    public function UpdateGame(request $request)
    {

        $game = Game::find($request->input('id'));
        $game->result = $request->input('result');
        // We have to check whether or not the players have been changed and therefore a color amount has to change.
        if ($game->white !== $request->input('white')) {
            $white_ranking = Ranking::where('user_id', $game->white)->first();
            if ($white_ranking !== null) {
                $white_ranking->color -= 1;
                $white_ranking->save();
            }
            $game->white = $request->input('white');
            $white_ranking = Ranking::where('user_id', $game->white)->first();
            if ($white_ranking !== null) {
                $white_ranking->color += 1;
                $white_ranking->save();
            }
        } else {
            $game->white = $request->input('white');
        }
        if ($game->black !== $request->input('black')) {
            $black_ranking = Ranking::where('user_id', $game->black)->first();
            if ($black_ranking !== null) {
                $black_ranking->color += 1;
                $black_ranking->save();
            }
            $game->black = $request->input('black');
            $black_ranking = Ranking::where('user_id', $game->black)->first();
            if ($black_ranking !== null) {
                $black_ranking->color -= 1;
                $black_ranking->save();
            }
        } else {
            $game->black = $request->input('black');
        }
        $game->save();

        return to_route('admin.games')->with('success', 'Resultaat opgeslagen');
    }

    public function AddGame($round)
    {
        $players = User::all();

        return view('admin.addgame')->with('round', $round)->with('players', $players);
    }

    public function storeGame(request $request)
    {

        $game = new Game;
        $game->white = $request->white;
        $game->black = $request->black;
        if ($request->black == 'Bye') {
            $game->result = '1-0';
        } else {
            $game->result = '0-0';
        }
        $game->round_id = $request->round;
        $game->save();

        // Update ranking of player
        $white_ranking = Ranking::where('user_id', $request->white)->first();
        if ($white_ranking !== null) {
            $white_ranking->color += 1;
            $white_ranking->save();
        }

        if ($request->black !== 'Bye') {
            $black_ranking = Ranking::where('user_id', $request->black)->first();

            if ($black_ranking !== null) {
                $black_ranking->color -= 1;
                $black_ranking->save();
            }
        }

        return redirect('/Admin')->with('success', 'Partij toegevoegd aan '.$request->round);
    }

    // User update functionality of the Admin
    public function UpdateUser(request $request)
    {
        $content = $request->getContent();
        $decoded = json_decode($content);

        $user = User::find($decoded->id);

        if (isset($decoded->email)) {
            $user->email = $decoded->email;
        } elseif (isset($decoded->rechten)) {
            $user->rechten = $decoded->rechten;
        } elseif (isset($decoded->rating)) {
            $user->rating = $decoded->rating;
        } elseif (isset($decoded->active)) {
            $user->active = $decoded->active;
        } elseif (isset($decoded->knsb_id)) {
            $user->knsb_id = $decoded->knsb_id;
        } elseif (isset($decoded->beschikbaar)) {
            $user->beschikbaar = $decoded->beschikbaar;
        } else {
            return redirect('/Admin/Users')->with('error', 'Je wilde niks aanpassen. Wat doe je hier?');
        }
        $user->save();
        $users = User::all()->sortBy('id');

        return redirect('/Admin/Users')->with(['success' => 'Gebruiker met '.$user->id.' is aangepast', 'users' => $users]);
    }

    // Destroy a user
    public function DestroyUser($id)
    {
        if (Gate::allows('admin', Auth::user())) {
            $presences = Presence::where('user_id', $id)->get();
            foreach ($presences as $presence) {
                $presence->delete();
            }
            $games = Game::where('white', $id)->orWhere('black', $id)->get();
            foreach ($games as $game) {
                if ($game->white == $id) {
                    $game->white = 'Lid verwijderd';
                } else {
                    $game->black = 'Lid verwijderd';
                }
                $game->save();
            }
            $user = User::findorFail($id);
            $user->delete();

            return redirect('/Admin')->with('success', 'Gebruiker verwijderd!');
        } else {
            return redirect('/')->with('error', 'Je hebt geen toegang tot administrator-paginas!');
        }
    }

    // New Season
    public function ResetSeason()
    {
        // Write results to file
        // Empty season tables
        DB::statement('SET foreign_key_checks=0');
        Ranking::truncate();
        Round::truncate();
        Presence::truncate();
        Game::truncate();
        $configs = Config::find(1);
        $configs->EndSeason = 0;
        $configs->save();
        DB::statement('SET foreign_key_checks=1');

        return redirect('/Admin')->with('success', 'Seizoen gereset');
    }

    // Presences
    public function InitPresences()
    {
        $users = User::where('beschikbaar', 1)->get();
        $rounds = Round::all();
        $presences = [];

        foreach ($users as $user) {
            foreach ($rounds as $round) {
                $presence_exist = Presence::where([['user_id', '=', $user->id], ['round', '=', $round->round]])->exists();

                if (! $presence_exist) {
                    $presences[] = [
                        'user_id' => $user->id,
                        'round' => $round->round,
                        'presence' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        if (! empty($presences)) {
            Presence::insert($presences);
        }

        $non_available_users = User::where('beschikbaar', 0)->where('updated_at', '>', now()->subDays(2))->get();
        foreach ($non_available_users as $user) {
            foreach ($rounds as $round) {
                if ($round->published == 0) {
                    Presence::where([['user_id', '=', $user->id], ['round', '=', $round->round]])->delete();
                }
            }
        }

        return redirect('/Admin')->with('success', 'Aanwezigheden aangepast');
    }

    public function AddPresence()
    {
        $users = User::all();
        $rounds = Round::all();

        return view('admin.addpresence')->with('players', $users)->with('rounds', $rounds);
    }

    public function storePresence(request $request)
    {
        $this->validate($request, [
            'round' => 'required',
            'presence' => 'required',
        ]);

        $round = $request->round;
        $user = $request->player;

        $presence_exist = Presence::where('user_id', $user)->where('round', $round)->get();

        if ($presence_exist->isEmpty()) {
            $presence = new Presence;
            $presence->user_id = $request->player;
            $presence->round = $round;
            $presence->presence = $request->presence;

            if ($presence->presence == 0) {

                if ($request->reason == 'Empty') {
                    return redirect('presences', 303)->with('error', 'Aanwezigheid niet aangepast! Je wilde een afmelding plaatsen, kies dan een reden!');
                }
                $games_white = Game::where('round_id', $round)->where('white', $user)->get();
                $games_black = Game::where('round_id', $round)->where('black', $user)->get();
                if ($games_white->isEmpty() && $games_black->isEmpty()) {

                    $game = new Game;
                    $game->white = $user;
                    $game->result = 'Afwezigheid';
                    $game->round_id = $round;
                    $game->black = $request->input('reason');
                    $game->save();
                } else {
                    return redirect('presences', 303)->with('error', 'Aanwezigheid niet aangepast! Je hebt al een partij in deze ronde gespeeld!');
                }
            }

            $presence->save();

            return redirect('/Admin', 303)->with('success', 'Aanwezigheid voor '.$request->player.' doorgegeven');
        }

        return redirect('/Admin', 303)->with('error', 'Er bestond al een aanwezigheid voor deze speler');
    }

    public function AddRanking()
    {
        $players = User::all();

        return view('admin.addranking')->with('players', $players);
    }

    public function storeRanking(request $request)
    {

        $ranking_exist = Ranking::where('user_id', $request->player)->first();

        if ($ranking_exist == null) {
            $ranking = new Ranking;
            $ranking->user_id = $request->player;
            $ranking->score = $request->score;
            $ranking->value = $request->value;
            $ranking->save();
        } else {
            $ranking_exist->score = $request->score;
            $ranking_exist->value = $request->value;
            $ranking_exist->save();
        }

        return redirect('/Admin')->with('success', 'Voor speler met id '.$request->player.' de ranking gemaakt (of aangepast) naar score & waarde: '.$request->score.' & '.$request->value.'!');
    }

    // Calculation
    public function InitCalculation($round)
    {
        ProcessCalculation::dispatch($round);

        // $calculation = new Calculation;
        // $calculation->Calculate($round);
        // No return necessary, return happens in Class of Calculation. But in case this fails, return to /Admin with a success message.
        return redirect('/Admin')->with('success', 'Ranglijst wordt bijgewerkt.');
    }

    private function sort_rating($a, $b)
    {
        return strnatcmp($b['rating'], $a['rating']);
    }

    // Ranking functionality of our Admin
    public function InitRanking()
    {
        $presences = Presence::where('round', 1)->get();
        $users_present = [];
        foreach ($presences as $presence) {
            $user_that_is_present = User::where('id', $presence->user_id)->first();

            $user_array = ['id' => $user_that_is_present->id, 'rating' => $user_that_is_present->rating];

            array_push($users_present, $user_array);
        }

        usort($users_present, [$this, 'sort_rating']);

        $i = Config::InitRanking('start');
        foreach ($users_present as $user) {

            $ranking_exist = Ranking::where('user_id', $user['id'])->get();
            if ($ranking_exist->isEmpty()) {
                $ranking = new Ranking;
                $ranking->user_id = $user['id'];
                $ranking->score = 0;
                $ranking->value = $i;
                $ranking->firstvalue = $i;
                $ranking->save();
                $i -= Config::InitRanking('step');
            }
        }

        return redirect('/Admin')->with('success', 'Ranglijst aangemaakt');
    }

    // Rating List functionality of our Admin ??
    public function RatingList()
    {
        return view('admin.ratinglist');
    }

    public function Instellingen(Request $request)
    {
        $configs = Config::find(1);
        $configs->roundsbetween_bye = $request->input('RoundsBetween_Bye');
        $configs->roundsbetween = $request->input('RoundsBetween');
        $configs->name = $request->input('Name');
        $configs->season = $request->input('Season');
        $configs->club = $request->input('Club');
        $configs->personal = $request->input('Personal');
        $configs->presence = $request->input('Presence');
        $configs->start = $request->input('Start');
        $configs->step = $request->input('Step');
        $configs->other = $request->input('Other');
        $configs->bye = $request->input('Bye');
        $configs->endseason = $request->input('EndSeason');
        $configs->announcement = $request->input('announcement');
        $configs->absencemax = $request->input('AbsenceMax');
        $configs->seasonpart = $request->input('SeasonPart');
        $configs->maximale_aanmeldtijd = $request->input('maximale_aanmeldtijd');
        // $configs->Admin = $request->input('Admin');
        $configs->save();
        $configs = Config::all();

        return Inertia::render('Admin/Configs')->with(['success' => 'Instellingen aangepast!', 'configs' => $configs]);
    }

    // Process the upload of the Rating List and generate user for player in case of non-existence.
    public function loadRatings(Request $request)
    {

        $file = $request->file('csv_file');

        // File Details
        $filename = $file->getClientOriginalName();

        $extension = $file->getClientOriginalExtension();
        $tempPath = $file->getRealPath();
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();

        // Valid File Extensions
        $valid_extension = ['csv'];

        // 2MB in Bytes
        $maxFileSize = 2097152;

        // Check file extension
        if (in_array(strtolower($extension), $valid_extension)) {

            // Check file size
            if ($fileSize <= $maxFileSize) {

                // Reading file
                $file = fopen($file, 'r');

                $importData_arr = [];

                $i = 0;

                while (($filedata = fgetcsv($file, 1000, ';')) !== false) {
                    $num = count($filedata);

                    // Skip first row (Remove below comment if you want to skip the first row)
                    if ($i == 0) {
                        $i++;

                        continue;
                    }
                    for ($c = 0; $c < $num; $c++) {
                        $importData_arr[$i][] = $filedata[$c];
                    }
                    $i++;
                }
                fclose($file);

                foreach ($importData_arr as $importData) {

                    $insertData = [
                        'knsb_id' => $importData[0],
                        'name' => $importData[1],
                        'email' => $importData[2],
                        'rating' => $importData[3],
                        'beschikbaar' => $importData[4],
                        'initPassWord' => $importData[5],
                    ];

                    // Check if KNSB_ID exist, then it is necessary to update, but no need to change password.
                    // Otherwise create.
                    $exist = User::where('knsb_id', $insertData['knsb_id'])->get();
                    if ($exist->isEmpty()) {
                        // Create so pass password and name. Settings are set when logged in for first time.
                        User::updateOrCreate(
                            [
                                'knsb_id' => $insertData['knsb_id'],

                            ],
                            [
                                'name' => htmlspecialchars($insertData['name']),
                                'email' => $insertData['email'],
                                'password' => Hash::make($insertData['initPassWord']),
                                'rating' => $insertData['rating'],
                                'beschikbaar' => $insertData['beschikbaar'],
                                'settings' => ['notifications' => '0'],
                            ]
                        );
                        User::where('knsb_id', $insertData['knsb_id'])->update(['settings' => ['notifications' => '0']]);
                        User::where('knsb_id', $insertData['knsb_id'])->update(['api_token' => Str::random(10)]);
                    } else {
                        // Update so don't pass name and password, but update email! // Still use updateOrCreate function though because it easier.
                        User::updateOrCreate(
                            [
                                'knsb_id' => $insertData['knsb_id'],
                            ],
                            [
                                'email' => $insertData['email'],
                                'rating' => $insertData['rating'],
                                'beschikbaar' => $insertData['beschikbaar'],

                            ]
                        );
                        User::where('knsb_id', $insertData['knsb_id'])->update(['settings' => ['notifications' => '0']]);
                        User::where('knsb_id', $insertData['knsb_id'])->update(['api_token' => Str::random(10)]);
                    }
                }

                return redirect('/Admin')->with('success', 'Ratinglijst is verwerkt!');
            }
        }
        return redirect('/Admin')->with('error', 'Er is iets mis gegaan. Probeer het opnieuw!');
    }

    // Process of file for Rounds (fields round and date)
    public function loadRounds(Request $request)
    {

        $file = $request->file('csv_file');

        // File Details
        $filename = $file->getClientOriginalName();

        $extension = $file->getClientOriginalExtension();
        $tempPath = $file->getRealPath();
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();

        // Valid File Extensions
        $valid_extension = ['csv'];

        // 2MB in Bytes
        $maxFileSize = 2097152;

        // Check file extension
        if (in_array(strtolower($extension), $valid_extension)) {

            // Check file size
            if ($fileSize <= $maxFileSize) {

                // Reading file
                $file = fopen($file, 'r');

                $importData_arr = [];

                $i = 0;

                while (($filedata = fgetcsv($file, 1000, ';')) !== false) {
                    $num = count($filedata);

                    // Skip first row (Remove below comment if you want to skip the first row)
                    if ($i == 0) {
                        $i++;

                        continue;
                    }
                    for ($c = 0; $c < $num; $c++) {
                        $importData_arr[$i][] = $filedata[$c];
                    }
                    $i++;
                }
                fclose($file);

                foreach ($importData_arr as $importData) {

                    $insertData = [
                        'round' => $importData[0],
                        'date' => $importData[1],
                    ];

                    // Update or Create (so if the round exists, it will update the date of the round)
                    // Otherwise it will create a new round.
                    Round::updateOrCreate(
                        [
                            'round' => $insertData['round'],
                            'uuid' => base64_encode(rand(0, 999999)),
                        ],
                        [
                            'uuid' => base64_encode(rand(0, 999999)),
                            'date' => $insertData['date'],

                        ]
                    );
                }

                return redirect('/Admin')->with('success', 'Rondes zijn succesvol verwerkt!');
            }
        }
        return redirect('/Admin')->with('error', 'Er is iets mis gegaan. Probeer het opnieuw!');
    }

    public function RecalculateRatop()
    {
        // Reset ratop for all rankings
        $rankings = Ranking::all();
        foreach ($rankings as $ranking) {
            $ranking->ratop = 0;
            $ranking->save();
        }

        // Iterate through all games to calculate ratop
        $games = Game::all();
        foreach ($games as $game) {
            // Update ratop for the white player
            if ($game->black !== 'Bye' && $game->black !== 'Other') {
                $opponent = User::find($game->black);
                if ($opponent) {
                    $opponentRating = $opponent->rating > 0 ? $opponent->rating : 1000; // Default to 1000 if no rating is set
                    $whiteRanking = Ranking::where('user_id', $game->white)->first();
                    if ($whiteRanking) {
                        $whiteRanking->ratop += $opponentRating;
                        $whiteRanking->save();
                    }
                }
            }

            // Update ratop for the black player
            if ($game->black !== 'Bye' && $game->black !== 'Other') {
                $opponent = User::find($game->white);
                if ($opponent) {
                    $opponentRating = $opponent->rating > 0 ? $opponent->rating : 1000; // Default to 1000 if no rating is set
                    $blackRanking = Ranking::where('user_id', $game->black)->first();
                    if ($blackRanking) {
                        $blackRanking->ratop += $opponentRating;
                        $blackRanking->save();
                    }
                }
            }
        }

        Log::info('Recalculated ratop for all players.');

        return redirect('/Admin')->with('success', 'Ratop values recalculated for all players.');
    }

    public function RecalculateTPR()
    {

        $calculation = new newCalculation;
        $rankings = Ranking::all();
        foreach ($rankings as $user) {

            if (($user->amount + $user->winter_amount) == 0) {
                $tpr = 0;
                $user->tpr = $tpr;
                $user->save();
            } else {

                $divide = ($user->gamescore + $user->winter_gamescore) / ($user->amount + $user->winter_amount);
                $average_rating = ($user->ratop + $user->winter_ratop) / ($user->amount + $user->winter_amount);
                $based_on_divide = $calculation->GetValueForTPR($divide);

                $tpr = $average_rating + $based_on_divide;
                $user->tpr = $tpr;
                $user->save();
            }
        }

        return 'Done';
    }

    public function PublishGames($Round)
    {
        $Round_to_Publish = Round::find($Round);
        $Round_to_Publish->published = 1;
        $Round_to_Publish->save();
        // Send notifications here
        $a = new PushController;
        $a->push('Admin', 'Partijen voor ronde '.$Round.' zijn aangemaakt!', 'Partijen', '2');

        return redirect('/Admin', 303)->with('success', 'Partijen zijn gepubliceerd!');
    }

    public function PublishRanking($Round)
    {
        $Round_to_Publish = Round::find($Round);
        $Round_to_Publish->ranking = 1;
        $Round_to_Publish->save();
        // Send notifications here
        $a = new PushController;
        $a->push('Admin', 'De stand is bijgewerkt, bekijk hem nu!', 'Stand', '1'); // Get results of round

        return redirect('/Admin', 303)->with('success', 'Stand is gepubliceerd!');
    }

    public function EditRanking($ranking)
    {

        $rank = Ranking::find($ranking);
        $player = User::find($rank->user_id);

        return view('admin.editranking')->with('player', $player)->with('ranking', $rank);
    }

    public function StoreUpdatedRanking(request $request)
    {
        $player = User::find($request->player);
        $rank = Ranking::where('user_id', $request->player)->first();
        $rank->score = $request->score;
        $rank->value = $request->value;
        $rank->save();

        return redirect('/Admin')->with('success', 'Ranking van '.$player->name.'bijgewerkt!');
    }

    public function BackRanking()
    {
        // Current round
        $b = new DetailsService;
        $currentRound = $b->CurrentRound();
        $round = Round::find($currentRound);

        $games = Game::where('round_id', $round->id)->get();

        foreach ($games as $game) {
            // White
            $currentScore_white = $b->CurrentScore($game->white, $round->id, $game->id);

            $rank_white = Ranking::where('user_id', $game->white)->first();

            $rank_white->score -= $currentScore_white;
            $rank_white->value = $rank_white->lastvalue;
            $rank_white->lastvalue = $rank_white->lastvalue2;
            $rank_white->amount -= 1;
            $rank_white->round -= 1;
            if ($game->black != 'Bye' || $game->black != 'Other')
                $currentScore_black = $b->CurrentScore($game->black, $round->id, $game->id);
            $rank_black = Ranking::where('user_id', $game->black)->first();
            $rank_black->score -= $currentScore_black;
            $rank_black->value = $rank_black->lastvalue;
            $rank_black->lastvalue = $rank_black->lastvalue2;
            $rank_black->amount -= 1;
            $rank_black->round = $rank_white->round - 1;
        }

        if ($game->result != 'Afwezigheid') {

            if (Str::contains($game->result, 'R')) {
                $result = explode('-', $game->result);
                $white_result = $result[0];
                $black_result = $result[1];
                if ($black_result == '1R') {
                    $black_result = '1';
                } else {
                    $black_result = $result[1];
                }
            } else {
                $result = explode('-', $game->result);
                $white_result = $result[0];
                $black_result = $result[1];
            }
            $rank_white->gamescore -= $white_result;
        }

        if ($game->black != 'Bye' || $game->black != 'Other') {
            $rating_black = User::find($game->black)->first();
            $rating_white = User::find($game->white)->first();
            $rank_white->ratop -= $rating_black->rating;
            $rank_black->ratop -= $rating_white->rating;
        }
        $rank_white->save();
        if ($game->black != 'Bye' || $game->black != 'Other') {
            $rank_black->save();
        }
        // Calculate new TPR
        if ($game->black != 'Bye' || $game->black != 'Other') {
            $a = new newCalculation;

            $rank_white->tpr = $a->calculateTPR($game->white);
            $rank_white->save();
            $rank_black->tpr = $a->calculateTPR($game->black);
            $rank_black->save();
        }


        // We hebben alle rankings voor partijen gecorrigeerd. Nu nog de rankings voor spelers die geen partij hadden

        $toCorrect = Ranking::where('round', $currentRound)->get();

        foreach ($toCorrect as $rank) {
            $rank->round -= 1;
            $rank->value = $rank->lastvalue;
            $rank->lastvalue = $rank->lastvalue2;
            $rank->save();
        }

        $round->processed = null;
        $round->save();

        return redirect('/Admin')->with('success', 'Ranglijst teruggezet');
        // score van een speler in de current round
        //
    }

    public function ResetRanking()
    {
        Ranking::truncate();
        $rounds = Round::all();
        foreach ($rounds as $round) {
            $round->processed = null;
            $round->save();
        }

        return redirect('/Admin')->with('success', 'Ranglijst verwijderd');
    }
}
