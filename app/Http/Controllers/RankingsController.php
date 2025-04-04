<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Ranking;
use App\Models\Game;
use App\Services\DetailsService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RankingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $ranking = Ranking::orderBy('score', 'desc')->orderBy('value', 'desc')->with('user:id,name')->get();
        $currentRound = DetailsService::CurrentRound();

        return Inertia::render('Rankings/index',['ranking' => $ranking, 'currentRound' => $currentRound]);
    }

    public function getDetails($userId){
        $ranking = Ranking::where('user_id', $userId)->with('user:id,name')->first();
        $details = new DetailsService();
        $games = $details->Games($userId);

        return Inertia::render('Rankings/details',['rank' => $ranking, 'games' => $games]);
    }
    public function admin()
    {

        if (Gate::allows('admin', Auth::user())) {

            return view('rankings.admin');
        } else {
            return redirect('rankings')->with('error', 'Je hebt geen toegang tot administrator-paginas!');
        }
    }

    public function CreateAdmin()
    {
        $users = User::all();
        foreach ($users as $user) {
            $ranking_exist = Ranking::where('user_id', $user->id)->get();
            if ($ranking_exist->isEmpty()) {
            }
        }
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
