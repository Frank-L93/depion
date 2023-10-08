@extends('layouts.app')

@section('content')
<div class="card text-black bg-light mb-3">
        <div class="card-header text-center">Rondes</div>
            <div class="card-body">
                <form method="POST" action="{{ route('RoundStore') }}">
                <div class="form-group">
                    <label for="date">Datum</label>
                    <input type="date" name="date" class="form-control"><br>
                    <label for="round">Ronde</label><br>
                    <input type="number" name="round" class="form-control"><br>
                    <div class="btn-group btn-group mr-2 float-right" role="group" aria-label="chooser">
                    <button name="rounds" type="submit" value="create" class="btn btn-success form-control">Creëer</button>
                    </div>

                </div>
               
            </form>
            </div>
        </div>
        
    </div>
@endsection