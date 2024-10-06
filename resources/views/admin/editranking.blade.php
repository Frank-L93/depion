@extends('layouts.app')

@section('content')
@auth
    <div class="card text-black bg-light mb-3">

        <div class="card-header text-center">
            Ranking</div>
            <div class="card-body">
           <form method='post' action='StoreUpdatedRanking'>
            @csrf
            <div class="col-md-6">
                <label for="player">Naam</label>
                <select id="player" name="player">
                    <option value="{{$player->id}}">{{$player->id}} {{$player->name}}</option>
                </select><br>
            </div>
                <div class="col-md-6">
                    <label for="value">Waarde</label>
                    <input type="number" name="value" id="value" value="{{$ranking->value}}"/><br>
                </div>
                <div class="col-md-6">
                        <label for="score">Score</label>
                        <input type="number" name="score" id="score" value="{{$ranking->score}}"/><br>
                </div>
                <div class="col-md-6">
                <div class="btn-group btn-group-lg mr-2" role="group" aria-label="chooser">
                    <button name="UpdatedRanking" type="submit" value="0" class="btn btn-success form-control">Pas Aan</button>
                </div>
            </div>
        </form>
             </div>

    </div>

@endauth
@endsection
