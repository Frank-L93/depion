@extends('layouts.app')

@section('content')
<div class="card text-black bg-light mb-3">
        <div class="card-header text-center">Rondes</div>
            <div class="card-body">
             <form method="POST" action="{{ route('RoundStore') }}">
                {{ csrf_field() }}
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
<div class="card text-black bg-light mb-3">
        <div class="card-header text-center">Meerdere Rondes</div>
            <div class="card-body">
          
                <form class="form-vertical" method="POST" action="{{ route('import_process_rounds') }}" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    
                    <div class="form-group{{ $errors->has('csv_file') ? ' has-error' : '' }}">
                        <label for="csv_file" class="col-md-4 control-label">Gebruik een CSV-bestand met de volgende kolommen: Ronde & Datum. De Datum noteren als JJJJ-MM-DD!</label>

                        <div class="col-md-6">
                            <input id="csv_file" type="file" class="form-control" name="csv_file" required>

                            @if ($errors->has('csv_file'))
                                <span class="help-block">
                                <strong>{{ $errors->first('csv_file') }}</strong>
                            </span>
                            @endif
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="btn-group btn-group mr-2 float-right" role="group" aria-label="chooser">
                            <button type="submit" class="btn btn-success form-control">Upload</button>
                            </div>
                    </div>
                </form>
            </div>
        </div> 
</div>
@endsection