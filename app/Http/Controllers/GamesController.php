<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Ranking;
use App\Models\Round;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class GamesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $user = auth()->user()->id;

        if (settings()->get('games') == 1) {
            $games = Game::where('white', $user)->orWhere('black', $user)->get();
        } else {
            $games = Game::all();
        }
        $users = User::all();
        $rounds = Round::all();

        $round_to_process = Round::where('processed', null)->where('published', 1)->first();
        if ($round_to_process == null) {
            $round_to_process = new Round;
            $round_to_process->id = 1;
        }

        $ranking = Ranking::orderBy('score', 'desc')->orderBy('value', 'desc')->get();

        return view('games.index')->with('rounds', $rounds)->with('ranking', $ranking)->with('games', $games)->with('users', $users)->with('current_user', $user)->with('round_to_process', $round_to_process);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     */
    public function edit($id): View
    {
        $game = Game::find($id);

        return view('games.edit')->with('game', $game);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     */
    public function update(Request $request, $id): Mixed
    {
        //
        $game = Game::find($id);

        $game->result = $request->input('result');

        if ($game->save()) {
            return redirect('Admin')->with('success', 'Aanwezigheid is aangepast');
        } else {
            return redirect('Admin')->with('error', 'Dit is niet jouw aanwezigheid die jij probeert aan te passen!');
        }
    }
}
