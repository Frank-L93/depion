<?php

namespace App\Http\Controllers;

use App\Models\Config;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class PagesController extends Controller
{
    public function index()
    {
        $settings = app('App\Models\Settings');
        if (!is_object($settings)) {
            $settings_json = json_decode($settings);
            App::setLocale($settings_json->language);
        } else {
            $settings_json = $settings;
            if ($settings_json->has('language')) {
                App::setLocale($settings_json->language);
            }
        }

        config(['app.name' => Config::CompetitionName()]);

        if ((Auth::check()) && (auth()->user()->remember_token == NULL)) {
            $user = Auth::user();

            $user->remember_token = 1;
            $user->save();
            return redirect('/settings')->with('success', 'Gelieve eerst je wachtwoord te wijzigen');
        }
        $dashboard = new DashboardController;

        $dashboard_games = $dashboard->GameDashBoard();
        $dashboard_rounds = $dashboard->RoundDashBoard();
        $dashboard_presences = $dashboard->PresenceDashBoard();
        $dashboard_absences = $dashboard->AbsenceDashBoard();
        $users = User::all();
        $announcement = Config::select('announcement')->first();
        if ($dashboard_rounds == "Geen rondes meer!") {
            return view('pages.index')->with('rounds', $dashboard_rounds)->with('config', $announcement);
        }

        return view('pages.index')->with('games', $dashboard_games)->with('rounds', $dashboard_rounds)->with('presences', $dashboard_presences)->with('absences', $dashboard_absences)->with('config', $announcement)->with('users', $users);
    }

    public function about()
    {
        return view('pages.about');
    }

    public function privacy()
    {
        return view('pages.privacy');
    }
}
