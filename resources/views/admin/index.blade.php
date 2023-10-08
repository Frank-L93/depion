@extends('layouts.app')

@section('content')
<div class="card-group">
    <div class="card text-black bg-light mb-3">
        <div class="card-header">
            Admin Dashboard van {{Auth::user()->name}}
        </div>
            <div class="card-body">
                @guest
                    Login om gebruik te maken van het Dashboard.
                @endguest
                @auth
                Je kunt hieronder gebruik maken van de verschillende Adminpagina's.
                <div class="card-group">
                <div class="card text-black bg-warning mb-3" style="max-width: 25em;">
                    <div class="card-header text-center">
                        Clubavond
                    </div>
                    <div class="card-body">
                    De volgende stappen moeten worden uitgevoerd om een clubavond af te handelen:
                    <ul>
                        <li>Genereer partijen voor aanwezigen op basis van stand</li>
                        <li>Vul scores in</li>
                        <li>Bereken stand</li>
                    </ul>
                
                    </div>
                </div>
                <div class="card text-black bg-light mb-3" style="max-width: 25em;">
        <div class="card-header text-center">
            Seizoen
        </div>
            <div class="card-body">
                De volgende stappen moeten worden uitgevoerd om een nieuw seizoen op te starten:
                <ul>
                    <li>Stel Seizoeneinde in op 1 onder Configuratie</li>
                    <li>Reset via knop die hieronder verschijnt</li>
                    <li>Verwijder eventuele gebruikers in je Database (Hier komt nog een functie voor!)</li> 
                    <li>Laad nieuwe Ratinglijst</li>
                    <li>Genereer Ranglijst</li>
                    <li>Creëer nieuwe rondes</li>
                    <li>Genereer aanwezigheden</li>
                    <li>Pas eventuele waarden aan</li>
                </ul>
                @foreach($configs as $config)
                @if($config->EndSeason == "0")
                @else
                <a href="/Admin/Reset" class="btn btn-secondary btn-xs pull-left">Reset seizoen</a>
                @endif
                @endforeach
        </div>
    </div>   
                </div>       
                @endauth
            </div>  
     </div>
</div>
<div id="_token" class="hidden" data-token="{{ csrf_token() }}"></div>
<ul class="nav nav-tabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" href="#ratinglist" role="tab" data-toggle="tab">Ratinglijst</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#config" role="tab" data-toggle="tab">Configuratie</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#users" role="tab" data-toggle="tab">Gebruikers</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#presences" role="tab" data-toggle="tab">Aanwezigheden</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#rankings" role="tab" data-toggle="tab">Ranglijst</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#rounds" role="tab" data-toggle="tab">Rondes</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#games" role="tab" data-toggle="tab">Partijen</a>
    </li>
   
    
</ul>
<div class="tab-content">
    <br>
    <div role="tabpanel" class="tab-pane active" id="ratinglist">
        <p>
            @include('admin.ratinglist')
        </p>
    </div>
     <div role="tabpanel" class="tab-pane" id="config">
        <p>
            @include('admin.config')
        </p>
    </div>
    <div role="tabpanel" class="tab-pane" id="users">
        <p>
            @include('admin.users')
        </p>
    </div>
    <div role="tabpanel" class="tab-pane" id="presences">
        
        <p>
            @include('admin.presences')
        </p>
    </div>
    <div role="tabpanel" class="tab-pane" id="rankings">
        <p>
            @include('admin.rankings')
        </p>
    </div>
    <div role="tabpanel" class="tab-pane" id="rounds">
        <p>
            @include('admin.rounds')
        </p>
    </div>
    <div role="tabpanel" class="tab-pane" id="games">
        <p>
            @include('admin.games')
        </p>
    </div>
</div>

  <!-- Editable Form Magic-->
  <!-- Used by the adminpages -->
        <script>
            $('#presencesTable').DataTable();

            $.fn.editable.defaults.mode = 'inline';
            $.fn.editable.defaults.params = function (params) {
                params._token = $("#_token").data("token");
                return params;
            };
            $('.result').editable({
                type: 'select',
                name:'result',
                url:'/Admin/Games/update',
                source: [
                            {value: "1-0", text: '1-0'},
                            {value: "0.5-0.5", text: '0.5-0.5'},
                            {value: "0-1", text: '0-1'},
{value: "0-1", text: '0-1'},
{value: "0-1R", text: '0-1R'},
{value: "1-0R", text: '1-0R'},
                        ]
            });
                $('.white').editable({
                    type: 'select',
                    name:'white',
                    url:'/Admin/Games/update',
                    source: "/Admin/users/list"
                });  
           
                $('.black').editable({
                    type: 'select',
                    name:'black',
                    url:'/Admin/Games/update',
                    source: "/Admin/users/list"
                });
            $('.email').editable({
                type: 'email',
                name: 'email',
                url:'/Admin/Users/update',
            });
            $('.rights').editable({
                type: 'select',
                name: 'rights',
                url:'/Admin/Users/update',
                source: [
                            {value: "0", text: 'Gebruiker'},
                            {value: "1", text: 'Competitieleider'},
                            {value: "2", text: 'Admin'}
                        ]
            });
            $('.rating').editable({
                type: 'number',
                name: 'rating',
                url:'/Admin/Users/update',
            });
            $('.active_user').editable({
                type: 'select',
                name: 'active',
                url:'/Admin/Users/update',
                  source: [
                            {value: "0", text: 'Niet Actief'},
                            {value: "1", text: 'Actief'},
                        ]
            });
             $('.knsb_id').editable({
                type: 'number',
                name: 'knsb_id',
                url:'/Admin/Users/update',
            }); 
             $('.beschikbaar').editable({
                type: 'select',
                name: 'beschikbaar',
                url:'/Admin/Users/update',
                  source: [
                            {value: "0", text: 'Standaard Niet Aanwezig'},
                            {value: "1", text: 'Standaard Aanwezig'},
                        ]
            }); 
        </script>
@endsection