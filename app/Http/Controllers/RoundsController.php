<?php

namespace App\Http\Controllers;

use App\Models\Round;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoundsController extends Controller
{


    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        $round = Round::all();

        return view('rounds.create')->with('rounds', $round);
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
            'date' => 'required',
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
}
