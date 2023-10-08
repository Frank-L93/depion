@auth
<div class="card text-black bg-light mb-3">
        <div class="card-header text-center">
           Ratinglijst</div>
            <div class="card-body">
                <form class="form-vertical" method="POST" action="{{ route('import_process') }}" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    
                    <div class="form-group{{ $errors->has('csv_file') ? ' has-error' : '' }}">
                        <label for="csv_file" class="col-md-4 control-label">Gebruik een CSV-bestand met de volgende kolommen: knsb_id, name, email, rating, deelname</label>

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
@endauth 