@extends('layouts.app')

@section('content')
@auth    
    <div class="card text-black bg-light mb-3">
       
        <div class="card-header text-center">
            Partij</div>
            <div class="card-body">
            <form method='post' action='{{route('storePresence')}}'>
                @csrf
                <label for="player">Naam</label>
                <select id="player" name="player">
                    @foreach($players as $player)
                    <option value="{{$player->id}}">{{$player->name}}</option>
                    @endforeach
                </select>
                <label for="round">Ronde</label>
                <select id="round" name="round">
                    @foreach($rounds as $round)
                    <option value="{{$round->round}}">{{Carbon\Carbon::parse($round->date)->format('j F Y')}}</option>
                    @endforeach
                </select><br>
                <label for="reason">Reden (alleen invullen als afwezig)</label><br>
                <select name="reason" class="form-control">
                    <option value="Empty"></option>
                    <option value="Other">Afwezig met Bericht</option>
                    <option value="Club">Afwezig i.v.m. clubactiviteit</option>
                    <option value="Personal">Afwezig i.g.v. force majeure (Ziek, Persoonlijke reden, bekend bij Wedstrijdleider)</option>
                </select>
                <label for="presence">Aanwezigheid</label><br>
                <div class="btn-group btn-group-lg mr-2" role="group" aria-label="chooser">
                    <button name="presence" type="submit" value="0" class="btn btn-danger form-control">Afwezig</button>
                    <button name="presence" type="submit" value="1" class="btn btn-success form-control">Aanwezig</button>
                </div>
            </form>
             </div>

    </div>   

@endauth 
@endsection
