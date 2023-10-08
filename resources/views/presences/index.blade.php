@extends('layouts.app')

@section('content')
    <div class="card">
        
        <div class="card-header text-center">Aanwezigheid <a href="/presences/create" class="btn btn-sm btn-secondary float-right" >Bepaal je nieuwe aanwezigheid</a></div>
            @if(count($presences)>0)
            <div class="card-body">
                <table class="table table-hover">
                    <thead class="thead-dark">
                            <th>Naam</th><th>Ronde</th><th>Aanwezig</th><th>Pas aan</th>
                        </thead>
                        @foreach($rounds as $round)

                        @foreach($presences as $presence)    
                            @if($presence->round === $round->round)
                            <tr>
                                <td><a href="/presences/{{$presence->id}}">{{$presence->user->name}}</a></td><td>{{Carbon\Carbon::parse($round->date)->format('j M Y')}}</td>
                                <td>
                                    @if($presence->presence === 0)
                                    <button name="presence" type="button" class="btn btn-danger btn-sm">Afwezig</button>
                                    @else
                                    <button name="presence" type="button" class="btn btn-success btn-sm">Aanwezig</button>
                                    @endif
                                </td>
                                <td><a href="/presences/{{$presence->id}}/edit" class="btn btn-sm btn-info"><img src="/assets/icons/pencil.svg" alt="" width="24" height="24"></a></td>
                            </tr>
                            @endif
                        @endforeach
                        @endforeach
                </table>
            </div>
        </div>
        <div class="card-footer clearfix">
             @endif
        </div> 
       
    </div>
@endsection