@extends('layouts.app')

@section('content')
    <div class="card">
        
        <div class="card-header text-center">Aanwezigheid</div>
            <div class="card-body">
                <form action='{{route('updatePresence', $presence->id)}}' method='post'>
              @csrf
                <div class="form-group">
                    <input type="hidden" name="id" value="{{$presence->id}}" class="form-control">
                    <label for="round">Ronde</label>
                    <input type="hidden" name="round" value="{{$presence->round}}" class="form-control">
                    <input type="text" name="date" value="{{ Carbon\Carbon::parse($round->date)->format('j M Y')}}" class="form-control" disabled>
                    
                    
                            
                        @if($presence->presence === 0)
                        <label for="presence">Aanwezigheid - Je bent momenteel afwezig</label><br>
                        <div class="btn-group btn-group-lg mr-2" role="group" aria-label="chooser">
                        <button name="presence" type="submit" value="1" class="btn btn-success form-control">Aanwezig</button>
                        @else
                        <div>
                            <label for="reason">Reden (alleen invullen als afwezig)</label><br>
                            <select name="reason" class="form-control">
                                <option value="Empty"></option>
                                <option value="Other">Afwezig met Bericht</option>
                            </select>
                        </div>
                        <label for="presence">Aanwezigheid - Je bent momenteel aanwezig</label><br>
                        <div class="btn-group btn-group-lg mr-2" role="group" aria-label="chooser">
                        <button name="presence" type="submit" value="0" class="btn btn-danger form-control">Afwezig</button>
                        
                        
                        @endif
                    </div>
                       </form>
                </div>
         
            </div>
        </div>
        <div class="card-footer clearfix">
           
        </div> 
       
    </div>
@endsection