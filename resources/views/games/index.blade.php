@extends('layouts.app')

@section('content')
@inject('Details', 'App\Services\DetailsService')
<div class="card text-black bg-light mb-3">
    <div class="card-header text-center">
        Partijen
    </div>
    <div class="card-body">
        @foreach($rounds as $round)
        <div class="card">
            <div class="card-header" id="heading{{$round->id}}">
                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapse{{$round->id}}" aria-expanded="false" aria-controls="collapse{{$round->id}}">
                    Ronde {{$round->id}} | {{Carbon\Carbon:: parse($round->date)->format('j M Y')}}
                </button>
            </div>
        </div>

        @if($round->published == 1)

        <div id="collapse{{$round->id}}" class="collapse" aria-labelledby="heading{{$round->id}}">
            <div class="card-body">
                <table class="table table-hover">
                    <thead class="thead-dark">
                        <th>Wit</th>
                        <th>Zwart</th>
                        <th>Resultaat</th>
                        <th>Jouw Score</th>
                    </thead>
                    @foreach($games as $game)
                    @if($game->result !== "Afwezigheid")
                    @if($round->id === $game->round_id)
                    <tr>
                        @foreach($users as $user)
                        @if($user->id === $game->white)
                        <td>{{$user->name}}</td>
                        @endif
                        @endforeach
                        @foreach($users as $user)
                        @if($user->id === intval($game->black))
                        <td>{{$user->name}}</td>
                        @endif
                        @endforeach
                        @if($game->black === "Bye")
                        <td>Bye</td>
                        @endif
                        <td>{{$game->result}}</td>
                        @if(($game->white === auth()->user()->id) || (intval($game->black) === auth()->user()->id))
                        <td>{{$Details->CurrentScore(auth()->user()->id, $round->id)}}</td>
                        @endif
                    </tr>
                    @endif
                    @elseif(auth()->user()->id === $game->white)
                    @if($round->id === $game->round_id)
                    <tr>
                        @foreach($users as $user)
                        @if($user->id === $game->white)
                        <td>{{$user->name}}</td>
                        @endif
                        @endforeach
                        @foreach($users as $user)
                        @if($user->id === intval($game->black))
                        <td>{{$user->name}}</td>
                        @endif
                        @endforeach
                        @if($game->black === "Bye")
                        <td>Bye</td>
                        @elseif($game->black === "Club")
                        <td>Afwezig i.v.m. Clubverplichting</td>
                        @elseif($game->black === "Personal")
                        <td>Afwezig i.g.v. Force Majeure</td>
                        @elseif($game->black === "Other" || $game->black === "Empty")
                        <td>Afwezig met Bericht</td>
                        @endif
                        <td>{{$game->result}}</td>
                        @if(($game->white === auth()->user()->id) || (intval($game->black) === auth()->user()->id))
                        <td>{{$Details->CurrentScore(auth()->user()->id, $round->id)}}</td>
                        @endif
                    </tr>
                    @endif
                    @endif
                    @endforeach
                </table>
            </div>
        </div>
        @endif
        @endforeach
    </div>
</div>
@endsection