@extends('layouts.app')

@section('content')
    <div class="card  text-black bg-warning mb-3" id="warning" style="display: none">
        <div class="card-header text-center">Waarschuwing!</div>
        <div class="card-body">Je hebt meerdere rondes geselecteerd. Wanneer je wilt afmelden met een specifieke reden, selecteer dan een datum.</div>
    </div>

    <div class="card">

        <div class="card-header text-center">Aanwezigheid</div>
            <div class="card-body">
             <form method='post' action='{{route('presences.store')}}'>
                @csrf
                <div class="form-group">
                    <label for="round[]">Ronde</label>
                    <select name="round[]" class="form-control" multiple onChange="showMultipleWarning(this)">                        
                        @foreach($rounds as $round)
                        <option value="{{$round->round}}">{{Carbon\Carbon::parse($round->date)->format('j F Y')}}</option>
                        @endforeach
                    </select>
                    <div>
                        <label for="reason">Reden (alleen invullen als afwezig)</label><br>
                        <select name="reason" class="form-control">
                            <option value="Empty"></option>
                            <option value="Other">Afwezig met Bericht</option>
                        </select>
                    </div>
                    <label for="presence">Aanwezigheid</label><br>
                    <div class="btn-group btn-group-lg mr-2" role="group" aria-label="chooser">
                        <button name="presence" type="submit" value="0" class="btn btn-danger form-control">Afwezig</button>
                        <button name="presence" type="submit" value="1" class="btn btn-success form-control">Aanwezig</button>
                       
                    </div>

                </div></form>
            </div>
        </div>
        <div class="card-footer clearfix">
        </div> 
       
    </div>
@endsection
<script>
    
    showMultipleWarning = function(select){
    var x = document.getElementById("warning");
        if(select.selectedOptions.length >= 2){
        x.style.display = "block";
    }else{
        x.style.display="none";
    };
}
</script>