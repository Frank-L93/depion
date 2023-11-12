@extends('layouts.app')

@section('content')
@auth
@inject('Details', 'App\Services\DetailsService')
<div class="card">
    <div class="card-header text-center">Ranglijst na ronde @if($Details->CurrentRound() == "Niet") @else{{$Details->CurrentRound()}}@endif</div>
@if($Details->CurrentRound() == "Niet")
<div class="card-body">
Ranglijst nog niet gepubliceerd
</div>
@else
    <div class="card-body">
        <table class="table table-hover">
            <thead class="thead-dark">
                <th>#</th>
                <th>Naam</th>
                <th>Score</th>
                <th>Waarde</th>
                <th>Winterscore</th>
                <th>Delta</th>
                @if(settings()->has('ranking') && (settings()->get('ranking') == 1))
                <th>Gespeelde Partijen</th>
                <th>Resultaat</th>
		<th>Partijscore</th>
                <th>TPR</th>
                @endif
            </thead>
            <?php
            $i = 1;
            ?>
            @foreach($ranking as $rank)
            <tr>
                <td><a href="#detail{{$rank->id}}" class="badge badge-pill badge-info" data-toggle="modal" data-target="#detail{{$rank->id}}"><?php echo $i;
                                                                                                                                                $i++; ?></a></td>
                <td>{{$rank->user->name}}</td>
                <td>{{number_format($rank->score, 2, '.', '')}}</td>
                <td>{{$rank->value}}</td>
                <td>{{number_format($Details->SummerScore($rank->user_id), 2, '.', '') }}</td>
                <td>{{number_format($rank->score - $Details->SummerScore($rank->user_id), 2, '.','')}}

                    @if(settings()->has('ranking') && (settings()->get('ranking') == 1))
                <td>{{$rank->amount}}</td>
                <td>{{$rank->gamescore}}</td>
		<td><?php $score = $rank->gamescore; $amount = $rank->amount; if($amount > 0){echo number_format($score/$amount*100, 2, '.', ''); }?></td>
                <td><?php echo round($rank->TPR); ?></td>
                @endif
            </tr>
            @endforeach

        </table>

    </div>
    <div class="card-footer clearfix">
    </div>
</div>
@foreach($ranking as $rank)
<div class="modal fade" id="detail{{$rank->id}}" tabindex="-1" role="dialog" aria-labelledby="detail{{$rank->id}}Title" aria-hidden="true" style="padding-right:50% !important">
    <div class="tableModal" style="padding-right:50% !important">
        <div class="table-cellModal">
            <div class="model-open">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="detail{{$rank->id}}Title">{{$rank->user->name}} </h5>
                            <hr>Waarde in ronde {{$Details->LastRound()}}: {{$rank->LastValue}} <br> Waarde voor ronde {{$Details->CurrentRound()}}: {{$rank->value}}
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <table class="table table-hover">
                                <thead class="thead-dark">
                                    <th>Wit</th>
                                    <th>Zwart</th>
                                    <th>Resultaat</th>
                                    <th>Ronde</th>
                                    <th>Score</th>
                                </thead>
                                @foreach($Details->Games($rank->user_id) as $game)
                                
                                <tr>
                                    <td>{{$Details->PlayerName($game->white)}}</td>
                                    <td>{{$Details->PlayerName($game->black)}}</td>
                                    <td>{{$game->result}}</td>
                                    <td>{{$game->round_id}}</td>
                                    <td>{{$Details->CurrentScore($rank->user_id, $game->round_id, $game->id)}}</td>
                                </tr>
                               @endforeach

                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Sluit</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach
@endif
@endauth
@endsection