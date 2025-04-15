<?php

namespace App\Http\Controllers;

use App\Models\Ranking;
use App\Models\User;
use App\Services\DetailsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class RankingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        $ranking = Ranking::orderBy('score', 'desc')->orderBy('value', 'desc')->with('user:id,name')->get();
        $currentRound = DetailsService::CurrentRound();

        return Inertia::render('Rankings/index', ['ranking' => $ranking, 'currentRound' => $currentRound]);
    }

    public function getDetails($userId)
    {
        $ranking = Ranking::where('user_id', $userId)->with('user:id,name')->first();
        $details = new DetailsService;
        $games = $details->Games($userId);

        return Inertia::render('Rankings/details', ['rank' => $ranking, 'games' => $games]);
    }
}
