<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Game;
use App\Models\Presence;
use App\Models\Round;
use App\Models\User;
use App\Models\Ranking;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;


class GamesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user()->id;

        if (settings()->get('games') == 1) {
            $games = Game::where('white', $user)->orWhere('black', $user)->get();
        } else {
            $games = Game::all();
        }
        $users = User::all();
        $rounds = Round::all();

        $round_to_process = Round::where('processed', NULL)->where('published', 1)->first();
        if ($round_to_process == NULL) {
            $round_to_process = new Round;
            $round_to_process->id = 1;
        }

        $ranking = Ranking::orderBy('score', 'desc')->orderBy('value', 'desc')->get();

        return view('games.index')->with('rounds', $rounds)->with('ranking', $ranking)->with('games', $games)->with('users', $users)->with('current_user', $user)->with('round_to_process', $round_to_process);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    public function edit($id)
    {
        $game = Game::find($id);
        return view('games.edit')->with('game', $game);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
