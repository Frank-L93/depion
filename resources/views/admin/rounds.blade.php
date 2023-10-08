@auth
    
    <div class="card text-black bg-light mb-3">
       
        <div class="card-header text-center">
            Rondes<a href="/Admin/Rounds/create" class="btn btn-sm btn-secondary float-right" >Creeër ronde</a></div>
            <div class="card-body">
                @if(count($rounds)>0)
                <table class="table table-hover">
                <thead class="thead-dark">
                    <th>Ronde</th><th>Datum</th><th>Publiceer</th><th>Publiceer</th><th>Pas aan</th><th>Verwijder</th>
                </thead>
                @foreach($rounds as $round)    
                    <tr>
                        <td><a href="/rounds/{{$round->id}}">{{$round->round}}</a></td><td>{{Carbon\Carbon::parse($round->date)->format('j M Y')}}</td>
                        <td>@if($round->published == 0)<a href="/rounds/{{$round->id}}/games" class="btn btn-sm btn-info">Partijen</a>@endif</td>
                        <td>@if($round->ranking == 0)<a href="/rounds/{{$round->id}}/rankings" class="btn btn-sm btn-info">Ranking</a>@endif</td>
                        <td><a href="/rounds/{{$round->id}}/edit" class="btn btn-sm btn-info"><img src="/assets/icons/pencil.svg" alt="" width="24" height="24"></a></td>
                        <td>
                                    {{html()->form('delete')->route('destroyRounds', $round->id)->open()}}
                                    {{html()->submit('Verwijder')}}
                                    {{ html()->form()->close() }}
                        </td>
                    </tr>
                @endforeach
            </table>
            @endif
        </div>

    </div>   

@endauth