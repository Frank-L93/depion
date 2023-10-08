@extends('layouts.app')

@section('content')
    <div class="card">
        
        <div class="card-header text-center">Partij</div>
            <div class="card-body">
                {!! Form::model($game, [
                    'method' => 'PATCH',
                    'route' => ['games.update', $game->id]
                ]) !!}
                <div class="form-group">
                    <input type="hidden" name="id" value="{{$game->id}}" class="form-control">
                    <label for="round">Resultaat</label>
                    <input type="text" name="result" value="{{$game->result}}" class="form-control" disabled>
                    
                    
                            
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
        <div class="card-footer clearfix">
           
        </div> 
       
    </div>
@endsection