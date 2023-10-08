@extends('layouts.app')

@section('content')
<div class="card text-black bg-light mb-3">
        <div class="card-header text-center">
            Instellingen
        </div>
        <div class="card-body">
            <div class="card">
                <div class="card-header">
                Wachtwoord
                </div>
                <div class="card-body">
                
                    <form class="form-horizontal" method="POST" action="{{ route('changePassword') }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('current-password') ? ' has-error' : '' }}">
                            <label for="new-password" class="col-md-4 control-label">Huidig Wachtwoord</label>

                            <div class="col-md-6">
                                <input id="current-password" type="password" class="form-control" name="current-password" required>

                                @if ($errors->has('current-password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('current-password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('new-password') ? ' has-error' : '' }}">
                            <label for="new-password" class="col-md-4 control-label">Nieuw Password</label>

                            <div class="col-md-6">
                                <input id="new-password" type="password" class="form-control" name="new-password" required>

                                @if ($errors->has('new-password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('new-password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="new-password-confirm" class="col-md-4 control-label">Bevestig nieuw Wachtwoord</label>

                            <div class="col-md-6">
                                <input id="new-password-confirm" type="password" class="form-control" name="new-password_confirmation" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                            <div class="btn-group btn-group-lg mr-2" role="group" aria-label="chooser">
                                <button type="submit" class="btn btn-success form-control">
                                    Pas aan
                                </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    Email
                </div>
                <div class="card-body">
                <form class="form-horizontal" method="POST" action="{{ route('changeEmail') }}">
                        {{ csrf_field() }}
                <div class="form-group">
                    <label for="email" class="col-md-4 control-label">E-mailadres</label>
                     <div class="col-md-6">
                        <input type="email" name="email" value="{{$user->email}}" class="form-control"/>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password" class="col-md-4 control-label">Wachtwoord</label>
                    <div class="col-md-6">
                        <input type="password" name="password" class="form-control" />
                    </div>
                </div>
                <div class="form-group">
                           <div class="col-md-6 col-md-offset-4">
                            <div class="btn-group btn-group-lg mr-2" role="group" aria-label="chooser">
                                <button type="submit" class="btn btn-success form-control">
                                    Pas aan
                                </button>
                                </div>
                            </div>
                        </div>
                </form>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                Voorkeuren
                </div>
                <div class="card-body">
                {!! Form::model($user, [
                    'method' => 'PATCH',
                    'route' => ['settings.update', $user->id],
                    'class' => 'form-horizontal'
                ]) !!}
                <div class="form-group">
                    <input type="hidden" name="id" value="{{$user->id}}" class="form-control">
                    <label for="games" class="col-md-4 control-label">Partijen</label>
                    <div class="col-md-6">
                   
                        <select name="games" class="form-control">
                            <option value="0" @if(array_key_exists('games', $settings)) @if($settings['games'] == 0) selected @endif @endif>Toon alle partijen</option>
                            <option value="1" @if(array_key_exists('games', $settings)) @if($settings['games'] == 1) selected @endif @endif>Toon eigen partijen</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="ranking" class="col-md-4 control-label">Stand</label>
                    <div class="col-md-6">
                        <select name="ranking" class="form-control">
                            <option value="0" @if(array_key_exists('ranking', $settings)) @if($settings['ranking'] == 0) selected @endif @endif>Beknopte stand</option>
                            <option value="1" @if(array_key_exists('ranking', $settings)) @if($settings['ranking'] == 1) selected @endif @endif>Uitgebreide stand</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="notifications" class="col-md-4 control-label">Notificaties<a href="#notifications_explain" class="badge badge-pill badge-info" data-toggle="modal" data-target="#notifications_explain">?</a></label>
                    <div class="col-md-6">

                        <select name="notifications" class="form-control">
                            <option value="0" @if(array_key_exists('notifications', $settings)) @if($settings['notifications'] == 0) selected @endif @endif>Geen Notificaties</option>
                            <option value="1" @if(array_key_exists('notifications', $settings)) @if($settings['notifications'] == 1) selected @endif @endif>Notificaties per e-mail en site</option>
                            <option value="2" @if(array_key_exists('notifications', $settings)) @if($settings['notifications'] == 2) selected @endif @endif>Notificaties per e-mail, Push Notificaties en site</option>
                            <option value="3" @if(array_key_exists('notifications', $settings)) @if($settings['notifications'] == 3) selected @endif @endif>Push Notificaties en site</option>
                            <option value="4" @if(array_key_exists('notifications', $settings)) @if($settings['notifications'] == 4) selected @endif @endif>Notificaties per SMS (Wordt aan gewerkt) en site</option>
                            <option value="5" @if(array_key_exists('notifications', $settings)) @if($settings['notifications'] == 5) selected @endif @endif>Notifcaties alleen op de site</option>
                        </select>
                        
                    </div>
                </div>
                <div class="form-group">
                    <label for="rss" class="col-md-4 control-label">RSS-feed</label>
                    <div class="col-md-6">

                        <select name="rss" class="form-control">
                            <option value="0" @if(array_key_exists('rss', $settings)) @if($settings['rss'] == 0) selected @endif @endif>Geen RSS</option>
                            <option value="1" @if(array_key_exists('rss', $settings)) @if($settings['rss'] == 1) selected @endif @endif>RSS</option>
                        </select>
                        
                    </div>
                </div>
                <div class="form-group">
                    @if($user->api_token !== NULL)
                    <label for="rss-link" class="col-md-4 control-label">RSS-feed link</label>
                    <div class="col-md-6">
                        <input class="form-control" type="text" value ="https://interndepion.nl/feed/{{$user->api_token}}"disabled>
                    </div>
                    @endif
                </div>
                <div class="form-group">
                    <label for="layout" class="col-md-4 control-label">Layout</label>
                    <div class="col-md-6">
                        <select name="layout" class="form-control">
                            <option value="app" @if(array_key_exists('layout', $settings)) @if($settings['layout'] == "app") selected @endif @endif>Standaard</option>
                            <option value="blue" @if(array_key_exists('layout', $settings)) @if($settings['layout'] == "blue") selected @endif @endif>Blauw</option>
                       </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="language" class="col-md-4 control-label">Taal</label>
                    <div class="col-md-6">
                        <select name="language" class="form-control">
                            <option value="nl" @if(array_key_exists('language', $settings)) @if($settings['language'] == "nl") selected @endif @endif>Nederlands</option>
                            <option value="en" @if(array_key_exists('language', $settings)) @if($settings['language'] == "en") selected @endif @endif>English</option>
                       </select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-6 col-md-offset-4">                    
                        <div class="btn-group btn-group-lg mr-2" role="group" aria-label="chooser">
                            <button name="settings" type="submit"  value="0" class="btn btn-success form-control">Pas aan</button>
                        </div>
                    </div>
                </div>                    
                {!! Form::close() !!}
                </div>
            </div>
            
        </div>
    </div>
<div class="modal fade" id="notifications_explain" tabindex="-1" role="dialog" aria-labelledby="notifications_explainTitle" aria-hidden="true" style="padding-right:50% !important">
                             <div class="tableModal" style="padding-right:50% !important">
        <div class="table-cellModal">
        <div class="model-open">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="notifications_explainTitle">Wanneer en hoe krijg je notificaties?</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p>Afhankelijk van je instellingen, apparaat en browser kun je verschillende soorten notificaties krijgen. Wanneer je voor de eerste keer inlogt zul je geen notificaties krijgen, pas als je hier in de instellingen een van de opties selecteert zul je notificaties krijgen.
                                    Notificaties zijn handige statusupdates: Wanneer de stand is bijgewerkt, een nieuwe ronde is ingedeeld of jouw partijen is bijgewerkt.

                                    <ul>
                                    <li>Notificaties per e-mail: Je krijgt een email met de statusupdate.</li>
                                    <li>Push Notificaties: Je krijgt een melding dat je browser push notificaties wilt tonen. Accepteer dit en je zult de statusupdates als melding vanuit je browser, ongeacht of deze open staat, krijgen. 
                                    <br>Dit werkt niet altijd maar wel in de volgende gevallen:
                                    <ul>
                                    <li>Chrome (niet iOS)</li>
                                    <li>Edge (de nieuwste versie)</li>
                                    <li>FireFox (niet iOS)</li>
                                    <li>Safari (Windows)</li>
                                    </ul>
                                    </li>
                                    <li>Notificaties per SMS: Je krijgt een SMS met de statusupdate. Bij het gebruik van deze optie wordt om een kleine donatie gevraagd gezien het versturen van SMS'jes geld kost.</li>
                                    <li>Notificaties op de website: Je krijgt de statusupdate alleen te zien op de website, boven in het scherm zul je een postvak zien waar je de meldingen in ontvangt. Hiervoor dien je ingelogd te zijn. Bij iedere soort notificatie, krijg je deze. Met deze optie krijg je alleen de notificatie op de website.</li>
                                    </p>
                                    <p>Wil je pushnotificaties ontvangen op je iPhone? Maak dan gebruik van de optie om een RSS-feed te gebruiken. Met de meeste RSS Reader apps, krijg je een notificatie als er namelijk een nieuw item daar aan is toegevoegd. En dat gebeurd!
                                    Jouw RSS-feed link is persoonlijk, dus je krijgt alleen je eigen berichten te zien. Werkt ook op andere apparaten;)</p>
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
@endsection