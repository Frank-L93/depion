<div class="card text-black bg-light mb-3">

        <div class="card-header text-center">
            Configuratie

        </div>
            <div class="card-body">
            {{ html()->form('post', '/Admin/Config')->open() }}
               @foreach($configs as $config)
                <table class="table table-hover">
                    <thead class="thead-dark">
                            <th>Instelling</th><th>Waarde</th>
                        </thead>

                            <tr><td>Competitienaam</td><td><input type="text" name="Name" id="Name" value="{{$config->Name}}" /></td></tr>
                            <tr><td>Seizoen</td><td><input type="text" name="Season" id="Season" value="{{$config->Season}}" /></td></tr>
                            <tr><td>Einde Seizoen</td><td><input type="number" step="1" max="1" min="0" name="EndSeason" value="{{$config->EndSeason}}" /><td></tr>
                            <tr><td>Tijdstip aanmeldtijd (als 00:00, niet gebruikt)</td><td><input type="time" name="maximale_aanmeldtijd" value="{{$config->maximale_aanmeldtijd}}" /><td></tr>
                            <tr><td>Hoogste waarde</td><td><input type="number" name="Start" id="Start" value="{{$config->Start}}" /></td></tr>
                            <tr><td>Stapgrootte</td><td><input type="number" name="Step" id="Step" value="{{$config->Step}}" /></td></tr>
                            <tr><td>Rondes tussen elkaar treffen</td><td><input type="number" name="RoundsBetween" id="RoundsBetween" value="{{$config->RoundsBetween}}" /></td></tr>
                            <tr><td>Rondes tussen bye opnieuw</td><td><input type="number" name="RoundsBetween_Bye" id="RoundsBetween_Bye" value="{{$config->RoundsBetween_Bye}}" /></td></tr>
                            <tr><td>Rondes per seizoenshelft</td><td><input id="SeasonPart" type="number" name="SeasonPart" value="{{$config->SeasonPart}}" /></td></tr>
                            <tr><td>Score voor Bye</td><td><input type="number" name="Bye" step="0.0001" max="1" min="0" id="Bye" value="{{$config->Bye}}" /></td></tr>
                            <tr><td>Score voor Aanwezigheid</td><td><input type="number" name="Presence" step="0.0001" max="5" min="0" id="Presence" value="{{$config->Presence}}" /></td></tr>
                            <tr><td>Score voor Afwezigheid namens Club</td><td><input type="number" name="Club" step="0.0001" max="1" min="0" id="Club" value="{{$config->Club}}" /></td></tr>
                            <tr><td>Score voor Afwezigheid Force Majeure (overig)</td><td><input type="number" name="Personal" step="0.0001" max="1" min="0" id="Personal" value="{{$config->Personal}}" /></td></tr>
                            <tr><td>Score voor Afwezigheid met Bericht</td><td><input type="number" name="Other" step="0.0001" max="1" min="0" id="Other" value="{{$config->Other}}" /></td></tr>
                            <tr><td>Maximaal aantal keren Afwezigheid met Bericht per seizoenshelft</td><td><input id="AbsenceMax" type="number" name="AbsenceMax" value="{{$config->AbsenceMax}}" /></td></tr>
                            <tr><td>Mededeling na afloop seizoen</td><td><input type="textarea" name="announcement" id="announcement" value="{{$config->announcement}}" /></td></tr>

                </table>
                @endforeach
                 <div class="btn-group btn-group-lg mr-2" role="group" aria-label="chooser">
                        <button name="presence" type="submit" value="1" class="btn btn-success form-control">Pas aan</button>

                    </div>
                 {{html()->form()->close()}}

            </div>
    </div>
