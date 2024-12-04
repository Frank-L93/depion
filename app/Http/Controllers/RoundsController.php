<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Round;
use App\Models\Presence;
use App\Models\User;

class RoundsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $rounds = Round::all();
        return view('rounds.index')->with('rounds', $rounds);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $round = Round::all();
        return view('rounds.create')->with('rounds', $round);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'round' => 'required',
            'date' => 'required'
        ]);

        $round = new Round;
        $round->uuid = base64_encode(rand(0, 999999));
        $round->round = $request->input('round');
        $round_exist = Round::where('round', $round->round)->get();
        if ($round_exist->isEmpty()) {
            $round->date = $request->input('date');
            $round->save();
        } else {
            return redirect('/rounds')->with('error', 'Deze ronde is al aangemaakt!');
        }
        return redirect('/rounds')->with('success', 'Ronde aangemaakt!');
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
        //
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
