@auth
    <div class="card text-black bg-light mb-3">
        <div class="card-header text-center">
            Ranglijst 
            @if(count($ranking)>0)
                <a class="btn btn-sm btn-secondary float-right" href="/Admin/RankingList/add" role="button">Voeg iemand toe</a>
            @else
                <a class="btn btn-sm btn-secondary float-right" href="/Admin/RankingList/create" role="button">Genereer Ranglijst</a>
            @endif
        </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead class="thead-dark">
                            <th>#</th><th>Naam</th><th>Score</th><th>Waarde</th><th>Pas aan</th>
                        </thead>
                        <?php
                        $i = 1;
                        ?>
                        @foreach($ranking as $rank)
                            <tr><td><a href="/ranking/{{$rank->id}}"><?php echo $i; $i++;?></a></td><td>{{$rank->user->name}}</td><td>{{$rank->score}}</td><td>{{$rank->value}}</td></tr>
                        @endforeach
                        
                </table>
            </div>
    </div>
@endauth
