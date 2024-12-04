@extends('layouts.app')

@section('content')
@auth
    <div class="card text-black bg-light mb-3">

        <div class="card-header text-center">
            Partij</div>
            <div class="card-body">
                <form action='{{route('storeGame')}}' method='post'>
                    @csrf
                <label for="white">Wit</label>
                <select id="white" name="white">
                    @foreach($players as $player)
                    <option value="{{$player->id}}">{{$player->name}}</option>
                    @endforeach
                </select>
                <label for="black">Zwart</label>
                <select id="black" name="black">
                    <option value="Bye">Bye</option>
                    @foreach($players as $player)
                    <option value="{{$player->id}}">{{$player->name}}</option>
                    @endforeach
                </select>
                <label for="round"></label><br>
                    <div class="btn-group btn-group-lg mr-2" role="group" aria-label="chooser">
                        <button id="round" name="round" type="submit" value="{{$round}}" class="btn btn-success form-control">Voeg partij toe aan ronde {{$round}}</button>
                    </div>
                </form>
             </div>

    </div>

@endauth
@endsection
