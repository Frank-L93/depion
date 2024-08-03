
    @if(auth()->user()->rechten == "2")
    <div class="card text-black bg-light mb-3">
        <div class="card-header text-center">
            Gebruikers
            <a class="btn btn-sm btn-secondary float-right" href="/register" role="button">Maak Gebruiker</a>
        </div>
            <div class="card-body">

                <table class="table table-hover">
                    <thead class="thead-dark">
                            <th>#</th><th>Naam</th><th>E-mail</th><th>Rechten</th><th>Rating</th><th>KNSB ID</th><th>Beschikbaar</th><th>Verwijder</th>
                        </thead>
                        @foreach($users as $user)
                            <tr><td><a href="/users/{{$user->id}}">{{$user->id}}</a></td><td>{{$user->name}}</td>
                            <td>
                                <a href="#" class="email" data-pk="{{$user->id}}" data-value="{{$user->email}}" data-title="Wijzig E-mail" class="editable editable-click" style="color: gray;" data-original-title="" title="">
                                        {{$user->email}}
                                 </a>
                            </td>
                            <td>
                                <a href="#" class="rights" data-pk="{{$user->id}}" data-value="{{$user->rechten}}" data-title="Wijzig Rechten" class="editable editable-click" style="color: gray;" data-original-title="" title="">
                                    @if($user->rechten == 2)
                                    Admin
                                    @else
                                    Gebruiker
                                    @endif
                                </a>
                            </td>
                            <td>
                            <a href="#" class="rating" data-pk="{{$user->id}}" data-value="{{$user->rating}}" data-title="Wijzig Rating" class="editable editable-click" style="color: gray;" data-original-title="" title="">
                                {{$user->rating}}
                            </a>
                            </td>
                            <td>
                            <a href="#" class="knsb_id" data-pk="{{$user->id}}" data-value="{{$user->knsb_id}}" data-title="Wijzig KNSB ID" class="editable editable-click" style="color: gray;" data-original-title="" title="">
                            {{$user->knsb_id}}
                            </a>
                            </td>
                            <td>
                            <a href="#" class="beschikbaar" data-pk="{{$user->id}}" data-value="{{$user->beschikbaar}}" data-title="Wijzig Beschikbaar" class="editable editable-click" style="color: gray;" data-original-title="" title="">
                            @if($user->beschikbaar == 1)
                            Standaard Beschikbaar
                            @else
                            Standaard Niet Beschikbaar
                            @endif
                            </a>
                            </td>
                            <td>
                                {{html()->form('delete')->route('destroyUser', $user->id)->class(['pull-right'])->open()}}
                                {{html()->submit('Verwijder')->class(['btn', 'btn-sm', 'btn-danger'])}}
                                    {{html()->form()->close() }}
                            </td>
                            </tr>
                        @endforeach
                </table>
            </div>
    </div>
    @endif
