@extends('layouts.app')

@section('content')
    @guest
    <div class="card bg-warning mb-3">
        <div class="card-header">{!! trans('pages.index.header_Guest') !!}</div>
        @endguest
        @auth
    <div class="card">
            <div class="card-header">{!! trans('pages.index.header_User') !!} {{Auth::user()->name}}</div>
    @endauth
                <div class="card-body">
                    @guest
                    {!! trans('pages.index.body_Guest') !!}
                    @endguest
                    @auth
                    <div class="row">
                        <div class="col-sm text-center">
                            <a href="/presences">
                                <img src="/assets/icons/stopwatch.svg" alt="" width="64" height="64">
                                <figcaption class="figure-caption">{!! trans('pages.index.presences') !!}</figcaption>
                            </a>
                        </div>
                        <div class="col-sm text-center">
                            <a href="/rankings">
                                <img src="/assets/icons/trophy.svg" alt="" width="64" height="64">
                                <figcaption class="figure-caption">{!! trans('pages.index.rankings') !!}</figcaption>
                            </a>
                        </div>
                        <div class="col-sm text-center">
                            <a href="/games">
                                <img src="/assets/icons/controller.svg" alt="" width="64" height="64">
                                <figcaption class="figure-caption">{!! trans('pages.index.games') !!}</figcaption>
                            </a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm text-center">
                            <a href="/settings">
                                <img src="/assets/icons/gear.svg" alt="" width="64" height="64">
                                <figcaption class="figure-caption">{!! trans('pages.index.settings') !!}</figcaption>
                            </a>
                        </div>
                        <div class="col-sm text-center">
                            <a href="https://www.depion.nl">
                                <img src="/assets/icons/Chess_pdt45.svg" alt="" width="64" height="64">
                                <figcaption class="figure-caption">{!! trans('pages.index.link') !!}</figcaption>
                            </a>
                        </div>
                        
                        <div class="col-sm text-center">
                            @if(Auth::user()->can('admin'))
                            <a href="/Admin">
                                <img src="/assets/icons/document-text.svg" alt="" width="64" height="64">
                                <figcaption class="figure-caption">{!! trans('pages.index.admin') !!}</figcaption>
                            </a>
                            @endif
                        </div>
                    </div>
                @endauth
            </div>
        @auth
            <div class="card-footer">
            </div>
        @endauth
    </div>
    @if($rounds === "Geen rondes meer!")
    <div class="card bg-warning">
        <div class="card-header">{!! trans('pages.index.no_rounds') !!}</div>
        <div class="card-body">
            <p>{{$config->announcement}}</p>
        </div>
    </div>
    @else
    <div class="card">
        <div class="card-header">{!! trans('pages.index.dashboard') !!}
                            @foreach($rounds as $round)
                                @if(Carbon\Carbon::parse($round->date)->format('j M Y') === Carbon\Carbon::parse(now())->format('j M Y'))
                                {!! trans('pages.main.Round') !!} {{$round->round}} | {!! trans('pages.index.today') !!} <?php $timediff = date_diff(Carbon\Carbon::parse($round->date), now()); if(Carbon\Carbon::parse($round->date) > now()){ echo "(over ".$timediff->h." uur, ".$timediff->i." minuten en ".$timediff->s." seconden)";}?>!
                                @else
                                {!! trans('pages.main.Round') !!} {{$round->round}} | {{Carbon\Carbon::parse($round->date)->format('j M Y')}}!
                                @endif
                            @endforeach
        </div>
        <div class="card-body">
            <div class="row">
                        <div class="col-sm text-center">
                             <img src="/assets/icons/play.svg" alt="" width="64" height="64">

                                <figcaption class="figure-caption">{!! trans('pages.index.games') !!}: @if($games == "Publicatie") Nog niet gepubliceerd @else {{$games->count()}} @endif</figcaption>
                        </div>
                        <div class="col-sm text-center">
                         <img src="/assets/icons/person-fill.svg" alt="" width="64" height="64">
                                <figcaption class="figure-caption">{!! trans('pages.index.present') !!}: 
 {{$presences->count()}}</figcaption></div><div class="col-sm text-center"><h3>Aanwezig</h3>
@foreach($presences as $presence)
@foreach($users as $user)
@if($user->id == $presence->user_id)
{{$user->name}}<br>
@endif
@endforeach
@endforeach
                        </div>
                        <div class="col-sm text-center">
                            <img src="/assets/icons/lock.svg" alt="" width="64" height="64">
                            <figcaption class="figure-caption">{!! trans('pages.index.absent_dashboard') !!}: {{$absences->count()}}
                        </div>
            </div>
        </div>
@if($games == "Publicatie")
@else
        @if($games->count() > 1)
        <div class="card-body">
            <table><thead><th>Wit</th><th>Zwart</th><th>Resultaat</th></thead>@foreach($games as $game)             
                            @if($game->result !== "Afwezigheid")
                                @if($round->round === $game->round_id)
                                    <tr>
                                        @foreach($users as $user)
                                            @if($user->id === $game->white)
                                                <td>{{$user->name}}</td>
                                            @endif
                                        @endforeach
                                        @foreach($users as $user)
                                            @if($user->id === intval($game->black))
                                                <td>{{$user->name}}</td>
                                            @endif 
                                        @endforeach
                                        @if($game->black === "Bye")
                                            <td>Bye</td>
                                        @endif
                                        <td>{{$game->result}}</td>
                                    </tr>
                                @endif
                            @endif
                        @endforeach
                                </table>
        </div>
        @endif
@endif
    </div>
    @endif

@endsection