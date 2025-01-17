<?php

namespace App\Http\Controllers;


use App\Http\Controllers\PushController;
use Illuminate\Http\Request;
use App\Models\Presence;
use App\Models\Round;
use App\Models\User;
use App\Models\Ranking;
use App\Models\Game;
use App\Actions\MatchGames;
use App\Models\Config;
use App\Models\Settings;
use App\Actions\Calculation;
use App\Helpers\TPRHelper;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Http\Controllers\iOSNotificationsController;
use App\Services\DetailsService;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;


global $k;
class AdminController extends Controller
{

    // Index page of our Admin
    public function admin()
    {
        $games = Game::all();
        $users = User::all();
        $presences = Presence::all();
        $rounds = Round::all();
        $configs = Config::all();
        $round_to_process = Round::where('processed', NULL)->orWhere('processed', 0)->first();
        if ($round_to_process == NULL) {
            $round_to_process = new Round;
            $round_to_process->id = 0;
        }
        $ranking = Ranking::orderBy('score', 'desc')->orderBy('value', 'desc')->get();
        return view('admin.index')->with('rounds', $rounds)->with('presences', $presences)->with('ranking', $ranking)->with('games', $games)->with('users', $users)->with('round_to_process', $round_to_process)->with('configs', $configs);
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
            'date' => 'required'
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
                return redirect('/Admin')->with('success', 'Aanwezigheid verwijderd!');
            } elseif ($games_black->isEmpty()) {
                foreach ($games_white as $game) {
                    if ($game->black == "Bye") {
                        $round = Round::find($game->round_id);
                        if ($round->processed == 1) {
                            return redirect('/Admin')->with('error', 'Deze aanwezigheid kan niet meer verwijderd worden.');
                        }
                        $presence->delete();
                        return redirect('/Admin')->with('success', 'Aanwezigheid verwijderd!');
                    } else {
                        return redirect('/Admin')->with('error', 'Deze aanwezigheid kan niet meer verwijderd worden');
                    }
                }
            }

            return redirect('/Admin')->with('error', 'Deze aanwezigheid kan niet meer verwijderd worden');
        } else {
            return redirect('/presences')->with('error', 'Je hebt geen toegang tot administrator-paginas!');
        }
    }

    public function DestroyRounds($id)
    {
        if (Gate::allows('admin', Auth::user())) {
            $round = Round::find($id);
            $round->delete();
            return redirect('/Admin')->with('success', 'Rondeverwijderd!');
        } else {
            return redirect('/rounds')->with('error', 'Je hebt geen toegang tot administrator-paginas!');
        }
    }

    // Games Functionallity of our Admin

    public function DestroyGames($id)
    {
        if (Gate::allows('admin', Auth::user())) {
            $game = Game::find($id);
            $game->delete();
            return redirect('/Admin')->with('success', 'Partij verwijderd!');
        } else {
            return redirect('/games')->with('error', 'Je hebt geen toegang tot administrator-paginas!');
        }
    }

    // Starting Matching process --> This you want somewehre else, but I dont know where. #help

    public function FillArrayPlayers($round) // loads all players that are needed to be paired in the specified round.
    {
        $players = array();
        $lower_value_set = 0;
        $presentPlayers = Presence::select('user_id')->where(['round' => $round, 'presence' => '1'])->get();
        foreach ($presentPlayers as $player) {
            $lowest_value_set = 0;
            array_push($players, $player->user_id);
            $lowest_value = Ranking::select('value')->orderBy('value', 'asc')->limit(1)->first();
            if ($lowest_value == NULL) {
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


                $player_closest_by = DB::table('users')->join('rankings', 'users.id', '=', 'rankings.user_id')->select('users.id', 'users.rating', 'rankings.value', DB::raw('ABS(' . $player_unranked->rating . ' - users.rating) as difference'))->orderby('difference')->limit(1)->first();


                if ($player_closest_by == null) {
                    //doe iets anders

                    if ($lowest_value_set ==  Config::InitRanking('start')) {
                        $ranking->value = $lowest_value_set;
                        $ranking->FirstValue = $lower_value_set;
                    } else {
                        $ranking->value = $lowest_value->value - 1;
                        $ranking->FirstValue = $lowest_value->value - 1;
                    }
                } else {
                    $ranking->value = $player_closest_by->value;
                    $ranking->FirstValue = $player_closest_by->value;
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
        $playerswithranking = array();
        foreach ($players as $player) {
            $player = Ranking::where('user_id', $player)->first();
            $check_already_paired = $this->checkPaired($player->user_id, $round);
            // In weird cases players may already be paired (i.e. new pairings?) so check it.
            if ($check_already_paired == true) {
            } else {
                $player_array = ["id" => $player->user_id, "rank" => $player->id, "value" => $player->value];
                array_push($playerswithranking, $player_array);
            }
        }
        usort($playerswithranking, array($this, 'sort_value')); // Sorting on value.

        $matches = new MatchGames;
        $matches->InitPairing($playerswithranking, $round); // Launch Pairing!
        return redirect('/Admin')->with('Success', 'Partijen aangemaakt!'); // Return will most likely not be called as in the pairing process, the last return that can be called is the return for the notifications which afterwards redirects to the Admin-page too. But for cases that this does not happen, this return is necessary.
    }

    // Helping function for UpdateGame, returns a json to fill editable list.
    public function List()
    {
        $users = User::all();
        $user_list = array();
        foreach ($users as $user) {
            array_push($user_list, ["value" => $user->id, "text" => $user->name]);
        }
        return json_encode($user_list);
    }

    // Game Changing functionality of our Admin

    public function UpdateGame(request $request)
    {

        $game = Game::find($request->input('pk'));
        if ($request->input('name') == 'result') {
            $game->result = $request->input('value');
        } elseif ($request->input('name') == 'white') {
            $game->white = $request->input('value');
        } elseif ($request->input('name') == 'black') {
            $game->black = $request->input('value');
        } else {
        }
        return $game->save();
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
        if($request->black == "Bye"){
            $game->result = "1-0";
        }
        else{
            $game->result = "0-0";
        }
        $game->round_id = $request->round;
        $game->save();

        // Update ranking of player
        $white_ranking = Ranking::where('user_id', $request->white)->first();
        if($white_ranking !== null)
        {
            $white_ranking->color = $white_ranking->color + 1;
            $white_ranking->save();
        }
        if(!$request->black == "Bye"){
        $black_ranking = Ranking::where('user_id', $request->black)->first();
            if($black_ranking !== null){
                $black_ranking->color = $black_ranking->color - 1;
                $black_ranking->save();
            }
        }
        return redirect('/Admin')->with('success', 'Partij toegevoegd aan ' . $request->round);
    }
    // User update functionality of the Admin
    public function UpdateUser(request $request)
    {

        $user = User::find($request->input('pk'));
        if ($request->input('name') == 'email') {
            $user->email = $request->input('value');
        } elseif ($request->input('name') == 'rights') {
            $user->rechten = $request->input('value');
        } elseif ($request->input('name') == 'rating') {
            $user->rating = $request->input('value');
        } elseif ($request->input('name') == 'active') {
            $user->active = $request->input('value');
        } elseif ($request->input('name') == 'knsb_id') {
            $user->knsb_id = $request->input('value');
        } elseif ($request->input('name') == 'beschikbaar') {
            $user->beschikbaar = $request->input('value');
        } else {
            return redirect('/Admin')->with('error', 'Je wilde niks aanpassen. Wat doe je hier?');
        }
        return $user->save();
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
                    $game->white == "Lid verwijderd";
                } else {
                    $game->black == "Lid verwijderd";
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
        DB::statement("SET foreign_key_checks=0");
        Ranking::truncate();
        Round::truncate();
        Presence::truncate();
        Game::truncate();
        $configs = Config::find(1);
        $configs->EndSeason = 0;
        $configs->save();
        DB::statement("SET foreign_key_checks=1");
        return redirect('/Admin')->with('success', 'Seizoen gereset');
    }

    // Presences
    public function InitPresences()
    {
        $users = User::where('beschikbaar', 1)->get();
        $rounds = Round::all();
        foreach ($users as $user) {
            foreach ($rounds as $round) {
                $presence_exist = Presence::where([['user_id', '=', $user->id], ['round', '=', $round->round]])->get();

                if ($presence_exist->isEmpty()) {
                    $presence = new Presence;
                    $presence->user_id = $user->id;
                    $presence->round = $round->round;
                    $presence->presence = 1;
                    $presence->save();
                }
            }
        }

        $non_available_users = User::where('beschikbaar', 0)->get();
        $rounds = Round::all();
        foreach($non_available_users as $user){
             //The date to compare
            $checkDate = date( "Y-m-d H:i:s", strtotime( "today -2 days" ) );

            if($user->updated_at > $checkDate){

            foreach($rounds as $round){
                if($round->published == 0){
                    $presence_exist = Presence::where([['user_id', '=', $user->id], ['round', '=', $round->round]])->first();
                    if(!$presence_exist == null)
                    {
                        $presence_exist->delete();
                    }
                }
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
            'presence' => 'required'
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

                if ($request->reason == "Empty") {
                    return redirect('presences')->with('error', 'Aanwezigheid niet aangepast! Je wilde een afmelding plaatsen, kies dan een reden!');
                }
                $games_white = Game::where('round_id', $round)->where('white', $user)->get();
                $games_black = Game::where('round_id', $round)->where('black', $user)->get();
                if ($games_white->isEmpty() && $games_black->isEmpty()) {

                    $game = new Game;
                    $game->white = $user;
                    $game->result = "Afwezigheid";
                    $game->round_id = $round;
                    $game->black = $request->input('reason');
                    $game->save();
                } else {
                    return redirect('presences')->with('error', 'Aanwezigheid niet aangepast! Je hebt al een partij in deze ronde gespeeld!');
                }
            }

            $presence->save();
            return redirect('/Admin')->with('success', 'Aanwezigheid voor ' . $request->player . ' doorgegeven');
        }
        return redirect('/Admin')->with('error', 'Er bestond al een aanwezigheid voor deze speler');
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
        return redirect('/Admin')->with('success', 'Voor speler met id ' . $request->player . ' de ranking gemaakt (of aangepast) naar score & waarde: ' . $request->score . ' & ' . $request->value . '!');
    }
    // Calculation
    public function InitCalculation($round)
    {
        $calculation = new Calculation;
        $calculation->Calculate($round);
        // No return necessary, return happens in Class of Calculation. But in case this fails, return to /Admin with a success message.
        return redirect('/Admin')->with('success', 'Ranglijst is bijgewerkt.');
    }

    private function sort_rating($a, $b)
    {
        return strnatcmp($b['rating'], $a['rating']);
    }
    // Ranking functionality of our Admin
    public function InitRanking()
    {
        $presences = Presence::where('round', 1)->get();
        $users_present = array();
        foreach ($presences as $presence) {
            $user_that_is_present = User::where('id', $presence->user_id)->first();

            $user_array = ["id" => $user_that_is_present->id, "rating" => $user_that_is_present->rating];

            array_push($users_present, $user_array);
        }

        usort($users_present, array($this, 'sort_rating'));

        $i = Config::InitRanking("start");
        foreach ($users_present as $user) {

            $ranking_exist = Ranking::where('user_id', $user["id"])->get();
            if ($ranking_exist->isEmpty()) {
                $ranking = new Ranking;
                $ranking->user_id = $user["id"];
                $ranking->score = 0;
                $ranking->value = $i;
                $ranking->FirstValue = $i;
                $ranking->save();
                $i = $i - Config::InitRanking("step");
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
        $configs->RoundsBetween_Bye = $request->input('RoundsBetween_Bye');
        $configs->RoundsBetween = $request->input('RoundsBetween');
        $configs->Name = $request->input('Name');
        $configs->Season = $request->input('Season');
        $configs->Club = $request->input('Club');
        $configs->Personal = $request->input('Personal');
        $configs->Presence = $request->input('Presence');
        $configs->Start = $request->input('Start');
        $configs->Step = $request->input('Step');
        $configs->Other = $request->input('Other');
        $configs->Bye = $request->input('Bye');
        $configs->EndSeason = $request->input('EndSeason');
        $configs->announcement = $request->input('announcement');
        $configs->AbsenceMax = $request->input('AbsenceMax');
        $configs->SeasonPart = $request->input('SeasonPart');
        $configs->maximale_aanmeldtijd = $request->input('maximale_aanmeldtijd');
        //$configs->Admin = $request->input('Admin');
        $configs->save();
        return redirect('/Admin')->with('success', 'Instellingen aangepast!');
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
        $valid_extension = array("csv");

        // 2MB in Bytes
        $maxFileSize = 2097152;

        // Check file extension
        if (in_array(strtolower($extension), $valid_extension)) {

            // Check file size
            if ($fileSize <= $maxFileSize) {




                // Reading file
                $file = fopen($file, "r");

                $importData_arr = array();

                $i = 0;

                while (($filedata = fgetcsv($file, 1000, ";")) !== FALSE) {
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

                    $insertData = array(
                        "knsb_id" => $importData[0],
                        "name" => $importData[1],
                        "email" => $importData[2],
                        "rating" => $importData[3],
                        "beschikbaar" => $importData[4],
                        "initPassWord" => $importData[5]
                    );

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
                                'settings' => ["notifications" => "0"],
                            ]
                        );
                        User::where('knsb_id', $insertData['knsb_id'])->update(['settings' => ["notifications" => "0"]]);
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
                        User::where('knsb_id', $insertData['knsb_id'])->update(['settings' => ["notifications" => "0"]]);
                        User::where('knsb_id', $insertData['knsb_id'])->update(['api_token' => Str::random(10)]);
                    }
                }
                return redirect('/Admin')->with('success', 'Ratinglijst is verwerkt!');
            }
        }
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
        $valid_extension = array("csv");

        // 2MB in Bytes
        $maxFileSize = 2097152;

        // Check file extension
        if (in_array(strtolower($extension), $valid_extension)) {

            // Check file size
            if ($fileSize <= $maxFileSize) {




                // Reading file
                $file = fopen($file, "r");

                $importData_arr = array();

                $i = 0;

                while (($filedata = fgetcsv($file, 1000, ";")) !== FALSE) {
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

                    $insertData = array(
                        "round" => $importData[0],
                        "date" => $importData[1]
                    );

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
    }

    public function RecalculateTPR()
    {

        $calculation = new Calculation;
        $rankings = Ranking::all();
        foreach ($rankings as $user) {


            if ($user->amount == 0) {
                $tpr = 0;
                $user->TPR = $tpr;
                $user->save();
            } else {

                $divide = $user->gamescore / $user->amount;
                $average_rating = $user->ratop / $user->amount;
                $based_on_divide = $calculation->GetValueForTPR($divide);

                $tpr = $average_rating + $based_on_divide;
                $user->TPR = $tpr;
                $user->save();
            }
        }

        return "Done";
    }
    public function PublishGames($Round)
    {
        $Round_to_Publish = Round::find($Round);
        $Round_to_Publish->published = 1;
        $Round_to_Publish->save();
        // Send notifications here
        $b = new iOSNotificationsController();
        $b->newFeedItem('Partijen', 'Partijen voor ronde' . $Round . ' zijn aangemaakt!', 'https://interndepion.nl/games', '2');
        $a = new PushController();
        $a->push('Admin', 'Partijen voor ronde ' . $Round . ' zijn aangemaakt!', 'Partijen', '2');
        return redirect('/Admin')->with('success', 'Partijen zijn gepubliceerd!');
    }
    public function PublishRanking($Round)
    {
        $Round_to_Publish = Round::find($Round);
        $Round_to_Publish->ranking = 1;
        $Round_to_Publish->save();
        // Send notifications here
        $b = new iOSNotificationsController();
        $b->newFeedItem('Stand', 'De stand is bijgewerkt, bekijk hem nu!', 'https://interndepion.nl/rankings', '1');
        $a = new PushController();
        $a->push('Admin', 'De stand is bijgewerkt, bekijk hem nu!', 'Stand', '1'); // Get results of round
        return redirect('/Admin')->with('success', 'Stand is gepubliceerd!');
    }

    public function EditRanking($ranking){

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
        $b = new DetailsService();
        $currentRound = $b->CurrentRound();
        $round = Round::find($currentRound);

        $games = Game::where('round_id', $round->id)->get();

        foreach($games as $game){
            // White
            $currentScore_white = $b->CurrentScore($game->white, $round->id, $game->id);

            $rank_white = Ranking::where('user_id', $game->white)->first();

            $rank_white->score = $rank_white->score - $currentScore_white;
            $rank_white->value = $rank_white->LastValue;
            $rank_white->LastValue = $rank_white->LastValue2;
            $rank_white->amount = $rank_white->amount - 1;
            $rank_white->round = $rank_white->round - 1;
            if($game->black == "Bye" || $game->black == "Other")
            {

            }else{
                $currentScore_black = $b->CurrentScore($game->black, $round->id, $game->id);
                $rank_black = Ranking::where('user_id', $game->black)->first();
                $rank_black->score = $rank_black->score - $currentScore_black;
                $rank_black->value = $rank_black->LastValue;
                $rank_black->LastValue = $rank_black->LastValue2;
                $rank_black->amount = $rank_black->amount - 1;
                $rank_black->round = $rank_white->round - 1;
            }

            if ($game->result == "Afwezigheid") {
            }else{
                if(Str::contains($game->result, 'R')){
                $result = explode("-", $game->result);
                $white_result = $result[0];
                $black_result = $result[1];
                    if($black_result == "1R")
                    {
                        $black_result = "1";
                    }else{
                    $black_result = $result[1];
                    }
                }
                else{
                    $result = explode("-", $game->result);
                    $white_result = $result[0];
                    $black_result = $result[1];
                }
                $rank_white->gamescore = $rank_white->gamescore - $white_result;
            }


            if($game->black == "Bye" || $game->black == "Other")
            {

            }else{
                $rating_black = User::find($game->black)->first();
                $rating_white = User::find($game->white)->first();
                $rank_white->ratop = $rank_white->ratop - $rating_black->rating;
                $rank_black->ratop = $rank_black->ratop - $rating_white->rating;
            }
            $rank_white->save();
            if($game->black == "Bye" || $game->black == "Other")
            {
            }
            else{
            $rank_black->save();
            }
            // Calculate new TPR
            if($game->black == "Bye" || $game->black == "Other")
            {
            }
            else{
                $a = new Calculation();

                $rank_white->tpr = $a->calculateTPR($game->white);
                $rank_white->save();
                $rank_black->tpr = $a->calculateTPR($game->black);
                $rank_black->save();
            }

        }
        // We hebben alle rankings voor partijen gecorrigeerd. Nu nog de rankings voor spelers die geen partij hadden

        $toCorrect = Ranking::where('round', $currentRound)->get();

        foreach($toCorrect as $rank){
            $rank->round = $rank->round - 1;
            $rank->value = $rank->LastValue;
            $rank->LastValue = $rank->LastValue2;
            $rank->save();
        }

        $round->processed = NULL;
        $round->save();
        return redirect('/Admin')->with('success', 'Ranglijst teruggezet');
        // score van een speler in de current round
        //
    }

    public function ResetRanking()
    {
        Ranking::truncate();
        $rounds = Round::all();
        foreach($rounds as $round){
            $round->processed = NULL;
            $round->save();
        }
        return redirect('/Admin')->with('success', 'Ranglijst verwijderd');
    }
}
