
    <div class="card text-black bg-light mb-3">
       
        <div class="card-header text-center">
        
                <a class="btn btn-sm btn-secondary float-left" href="/Admin/Presences/create" role="button">Genereer Aanwezigheden</a>
         
            Aanwezigheid
        </div>
            <div class="card-body">
                @if(count($presences)>0)
                <table class="table table-hover" id="presencesTable">
                    <thead class="thead-dark">
                            <th>Naam</th><th>Ronde</th><th>Aanwezig</th><th>Verwijder</th>
                        </thead>
                        @foreach($presences as $presence)    
                            <tr>
                                <td><a href="/presences/{{$presence->id}}">{{$presence->user->name}}</a></td><td>{{$presence->round}}</td><td>{{$presence->presence}}</td>
                                <td>
                                    {{html()->form('delete')->route('destroyPresences', $presence->id)->open()}}
                                    {{html()->submit('Verwijder')}}
                                    {{ html()->form()->close() }}
                                    
                                </td>
                            </tr>
                        @endforeach
                </table>
                <a href="/Admin/Presence/Add" class="btn btn-sm btn-secondary">Voeg aanwezigheid toe</a>
                @endif
            </div>
    </div>

