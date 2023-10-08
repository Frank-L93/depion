@extends('layouts.app')

@section('content')
@auth    
    <div class="card text-black bg-light mb-3">
       
        <div class="card-header text-center">
            Ranking</div>
            <div class="card-body">
           <form method='post' action='storeRanking'>
            @csrf
            <div class="col-md-6">
                <label for="player">Naam</label>
                <select id="player" name="player">
                    @foreach($players as $player)
                    <option value="{{$player->id}}">{{$player->id}} {{$player->name}}</option>
                    @endforeach
                </select><br>
            </div>
                <div class="col-md-6">
                    <label for="value">Waarde</label>
                    <input type="number" name="value" id="value" /><br>
                </div>
                <div class="col-md-6">
                        <label for="score">Score</label>
                        <input type="number" name="score" id="score" /><br>
                </div>
                <div class="col-md-6">
                <div class="btn-group btn-group-lg mr-2" role="group" aria-label="chooser">
                    <button name="ranking" type="submit" value="0" class="btn btn-success form-control">Voeg toe</button>
                </div>
            </div>
        </form>
             </div>

    </div>   

@endauth 
@endsection