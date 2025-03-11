<?php

namespace App\Http\Controllers;

use App\iOSNotification;
use App\Feedable;
use App\FeedItem;
use App\Models\User;
use App\Models\Game;
use App\Models\Ranking;

class iOSNotificationsController extends Controller implements Feedable
{

    public static function getFeedItems($API_Token)
    {

        $users = User::where('api_token', $API_Token)->get();

        if ($users->isEmpty()) {
            return view('pages.index')->with('error', 'You are not allowed to read this feed');
        } else {
            foreach ($users as $user) {
                $items = iOSNotification::where('user_id', $user->id)->get();
                $meta = array();
                $meta = [
                    'title' => 'De Pion',
                    'description' => 'Notificaties Intern De Pion',
                    'language' => 'nl-NL',
                    'type' => 'application/atom+xml'
                ];
                return view('vendor.feed.atom')->with('items', $items)->with('meta', $meta);
            }
        }
    }

    public function newFeedItem($title, $summary, $link, $type)
    {
        if ($type == 3) {
            $users = User::where('id', '1')->get();
        } else {
            $users = User::all();
        }


        foreach ($users as $user) {

            if (($user->settings()->has('rss') == true) && ($user->settings()->get('rss') == 1)) {

                // Match
                if ($type == 2) {
                    $game = Game::where('white', $user->id)->orWhere('black', $user->id)->latest()->first();
                    if($game == null){
                        return;
                    }
                    $white = User::select('name')->where('id', $game->white)->first();

                    if ($game->black == "Bye" || $game->result == "Afwezigheid") {
                        $black = "Bye of Afwezigheid";
                    } else {
                        $black = User::select('name')->where('id', $game->black)->first();
                        $black = $black->name;
                    }
                    $summary = "Nieuwe partijen zijn ingedeeld. <br> Jouw partij is: <br>" . $white . " - " . $black;
                } elseif ($type == 1) {
                    $i = 1;
                    $summary = "Er is een nieuwe stand! Hieronder volgt de nieuwe top 3<hr>";
                    $data = Ranking::orderBy('score', 'desc')->orderBy('value', 'desc')->take(3)->get();
                    foreach ($data as $ranking) {
                        $summary .= $i . " | " . $ranking->user->name . " | " . $ranking->score . " | " . $ranking->value . "<br>";
                        $i++;
                    }
                }
                $item = new iOSNotification();
                $item->user_id = $user->id;
                $item->title = $title;
                $item->summary = $summary;
                $item->link = $link;
                $item->author = "De Pion";
                $item->save();
            }
        }
        return true;
    }
    public function toFeedItem()
    {
    }
}
