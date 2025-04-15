<?php

namespace App\Http\Controllers;

use App\Models\Config;
use App\Models\Game;
use App\Models\Presence;
use App\Models\Round;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PresencesController extends Controller
{
    /**
     * Display a listing of the resource based on user_id.
     *
     * @return View
     */
    public function index(): View
    {
        $user_id = auth()->user()->id;
        $user = User::find($user_id);
        $round = Round::all();

        return view('presences.index')->with('presences', $user->presences)->with('rounds', $round);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create()
    {
        $round = Round::all();

        return view('presences.create')->with('rounds', $round);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $this->validate($request, [
            'round' => 'required',
            'presence' => 'required',
        ]);

        foreach ($request->input('round') as $round) {

            $user = auth()->user()->id;
            $presence_exist = Presence::where('user_id', $user)->where('round', $round)->get();

            if ($presence_exist->isEmpty()) {
                $presence = new Presence;
                $presence->user_id = auth()->user()->id;
                $presence->round = $round;
                $presence->presence = $request->input('presence');

                if ($presence->presence == 0) {

                    if ($request->input('reason') == 'Empty') {
                        return redirect('/presences')->with('error', 'Aanwezigheid niet aangepast! Je wilde een afmelding plaatsen, kies dan een reden!');
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
                        return redirect('presences')->with('error', 'Aanwezigheid niet aangepast! Je hebt al een partij in deze ronde gespeeld!');
                    }
                } else {
                    // Check if now is later than last time
                    $round_object = Round::where('round', $round)->withCasts(['date' => 'datetime'])->get();
                    $round_date = $round_object[0]->date;
                    $day_before_round_date = $round_date->add('-1 day');
                    $time_to_check = explode(':', Config::MaxAanmeldTijd());
                    $hour = $time_to_check[0] * 1;
                    $minutes = $time_to_check[1] * 1;
                    if ($hour != 0 && $minutes != 0) {
                        $check_date = $day_before_round_date->setTime($hour, $minutes);

                        $current_time = now()->timezone('Europe/Amsterdam');
                        if ($current_time >= $check_date) {
                            return redirect('/presences')->with('error', 'Er ging iets fout vanaf ronde ' . $round . ' (' . $round_date->format('d-m-Y') . ')! Je kunt niet meer aanmelden voor deze ronde, dit kon tot maximaal: ' . Config::MaxAanmeldTijd());
                        }
                    }

                }
                $presence->save();

            } else {
                $round_info = Round::where('round', $round)->first();

                return redirect('/presences')->with('error', 'Er ging iets fout vanaf ronde '.$round.'('.$round_info->date.'). Als je je aanwezigheid wilt aanpassen, gebruik dan het potloodje!');

            }
        }

        return redirect('/presences')->with('success', 'Aanwezigheid doorgegeven!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Mixed
     */
    public function show($id)
    {
        $presence = Presence::find($id);
        $round = Round::where('round', $presence->round)->first();
        if ($presence->user_id == auth()->user()->id || Gate::allows('admin', Auth::user())) {
            return view('presences.show')->with('presence', $presence)->with('round', $round);
        } else {
            return redirect('/presences')->with('error', 'Dit is niet jouw aanwezigheid die jij probeert te bekijken!');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return View
     */
    public function edit($id): View
    {
        $presence = Presence::find($id);
        $round = Round::where('round', $presence->round)->first();

        return view('presences.edit')->with('presence', $presence)->with('round', $round);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {

        $presence = Presence::find($id);

        if ($presence->user_id == auth()->user()->id || Gate::allows('admin', Auth::user())) {
            $user = $presence->user_id;
            $presence->presence = $request->input('presence');

            if ($presence->presence == 0) {
                if ($request->input('reason') == 'Empty') {
                    return redirect('/presences')->with('error', 'Aanwezigheid niet aangepast! Je wilde een afmelding plaatsen, kies dan een reden!');
                }

                $games_white = Game::where('round_id', $presence->round)->where('white', $user)->get();
                $games_black = Game::where('round_id', $presence->round)->where('black', $user)->get();
                if ($games_white->isEmpty() && $games_black->isEmpty()) {

                    $game = new Game;
                    $game->white = $user;
                    $game->result = 'Afwezigheid';
                    $game->round_id = $presence->round;
                    $game->black = $request->input('reason');
                    $game->save();
                } else {
                    // Notify Admin that player wants to set absence while already has a game for this round.
                    $a = new PushController;
                    $a->push('none', 'Nieuwe late afmelding van '.$user.' voor ronde '.$presence->round, 'Afmelding', '3');

                    return redirect('/presences')->with('error', 'Aanwezigheid niet aangepast! Je hebt al een partij in deze ronde gespeeld!');
                }
            } else { // We are updating the presences. It now turns out to be the player is present, therefore, the game with the absence, should be deleted from the database.

                $game = Game::where([
                    ['round_id', '=', $presence->round],
                    ['white', '=', $user],
                ])->get();

                if (!$game->isEmpty()) {
                    foreach ($game as $game_to_delete) {
                        $game_to_delete->delete();
                    }
                }
            }
            $presence->save();

            return redirect('/presences')->with('success', 'Aanwezigheid is aangepast');
        } else {
            return redirect('/presences')->with('error', 'Dit is niet jouw aanwezigheid die jij probeert aan te passen!');
        }
    }
}
