import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

export default function Config({ configs }) {
    const { data, setData, post, processing, errors } = useForm({
        Name: configs[0].name,
        Season: configs[0].season,
        EndSeason: configs[0].endseason,
        maximale_aanmeldtijd: configs[0].maximale_aanmeldtijd,
        Start: configs[0].start,
        Step: configs[0].step,
        RoundsBetween: configs[0].roundsbetween,
        RoundsBetween_Bye: configs[0].roundsbetween_bye,
        SeasonPart: configs[0].seasonpart,
        Bye: configs[0].bye,
        Presence: configs[0].presence,
        Club: configs[0].club,
        Personal: configs[0].personal,
        Other: configs[0].other,
        AbsenceMax: configs[0].absencemax,
        announcement: configs[0].announcement,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/Admin/Config');
    };

    return (
        <AdminLayout>

            <div className="card text-black bg-light mb-3">
                <div className="card-header text-center">Configuratie</div>
                <div className="card-body">
                    <form onSubmit={handleSubmit}>
                        <table className="table table-hover">
                            <thead className="thead-dark">
                                <tr>
                                    <th>Instelling</th>
                                    <th>Waarde</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Competitienaam</td>
                                    <td>
                                        <input
                                            type="text"
                                            name="Name"
                                            value={data.Name}
                                            onChange={(e) => setData('Name', e.target.value)}
                                        />
                                    </td>
                                </tr>
                                <tr>
                                    <td>Seizoen</td>
                                    <td>
                                        <input
                                            type="text"
                                            name="Season"
                                            value={data.Season}
                                            onChange={(e) => setData('Season', e.target.value)}
                                        />
                                    </td>
                                </tr>
                                <tr>
                                    <td>Einde Seizoen</td>
                                    <td>
                                        <input
                                            type="number"
                                            step="1"
                                            max="1"
                                            min="0"
                                            name="EndSeason"
                                            value={data.EndSeason}
                                            onChange={(e) => setData('EndSeason', e.target.value)}
                                        />
                                    </td>
                                </tr>
                                <tr>
                                    <td>Tijdstip aanmeldtijd (als 00:00, niet gebruikt)</td>
                                    <td>
                                        <input
                                            type="time"
                                            name="maximale_aanmeldtijd"
                                            value={data.maximale_aanmeldtijd}
                                            onChange={(e) => setData('maximale_aanmeldtijd', e.target.value)}
                                        />
                                    </td>
                                </tr>
                                <tr>
                                    <td>Hoogste waarde</td>
                                    <td>
                                        <input
                                            type="number"
                                            name="Start"
                                            value={data.Start}
                                            onChange={(e) => setData('Start', e.target.value)}
                                        />
                                    </td>
                                </tr>
                                <tr>
                                    <td>Stapgrootte</td>
                                    <td>
                                        <input
                                            type="number"
                                            name="Step"
                                            value={data.Step}
                                            onChange={(e) => setData('Step', e.target.value)}
                                        />
                                    </td>
                                </tr>
                                <tr>
                                    <td>Rondes tussen elkaar treffen</td>
                                    <td>
                                        <input
                                            type="number"
                                            name="RoundsBetween"
                                            value={data.RoundsBetween}
                                            onChange={(e) => setData('RoundsBetween', e.target.value)}
                                        />
                                    </td>
                                </tr>
                                <tr>
                                    <td>Rondes tussen bye opnieuw</td>
                                    <td>
                                        <input
                                            type="number"
                                            name="RoundsBetween_Bye"
                                            value={data.RoundsBetween_Bye}
                                            onChange={(e) => setData('RoundsBetween_Bye', e.target.value)}
                                        />
                                    </td>
                                </tr>
                                <tr>
                                    <td>Rondes per seizoenshelft</td>
                                    <td>
                                        <input
                                            type="number"
                                            name="SeasonPart"
                                            value={data.SeasonPart}
                                            onChange={(e) => setData('SeasonPart', e.target.value)}
                                        />
                                    </td>
                                </tr>
                                <tr>
                                    <td>Score voor Bye</td>
                                    <td>
                                        <input
                                            type="number"
                                            step="0.0001"
                                            max="1"
                                            min="0"
                                            name="Bye"
                                            value={data.Bye}
                                            onChange={(e) => setData('Bye', e.target.value)}
                                        />
                                    </td>
                                </tr>
                                <tr>
                                    <td>Score voor Aanwezigheid</td>
                                    <td>
                                        <input
                                            type="number"
                                            step="0.0001"
                                            max="5"
                                            min="0"
                                            name="Presence"
                                            value={data.Presence}
                                            onChange={(e) => setData('Presence', e.target.value)}
                                        />
                                    </td>
                                </tr>
                                <tr>
                                    <td>Score voor Afwezigheid namens Club</td>
                                    <td>
                                        <input
                                            type="number"
                                            step="0.0001"
                                            max="1"
                                            min="0"
                                            name="Club"
                                            value={data.Club}
                                            onChange={(e) => setData('Club', e.target.value)}
                                        />
                                    </td>
                                </tr>
                                <tr>
                                    <td>Score voor Afwezigheid Force Majeure (overig)</td>
                                    <td>
                                        <input
                                            type="number"
                                            step="0.0001"
                                            max="1"
                                            min="0"
                                            name="Personal"
                                            value={data.Personal}
                                            onChange={(e) => setData('Personal', e.target.value)}
                                        />
                                    </td>
                                </tr>
                                <tr>
                                    <td>Score voor Afwezigheid met Bericht</td>
                                    <td>
                                        <input
                                            type="number"
                                            step="0.0001"
                                            max="1"
                                            min="0"
                                            name="Other"
                                            value={data.Other}
                                            onChange={(e) => setData('Other', e.target.value)}
                                        />
                                    </td>
                                </tr>
                                <tr>
                                    <td>Maximaal aantal keren Afwezigheid met Bericht per seizoenshelft</td>
                                    <td>
                                        <input
                                            type="number"
                                            name="AbsenceMax"
                                            value={data.AbsenceMax}
                                            onChange={(e) => setData('AbsenceMax', e.target.value)}
                                        />
                                    </td>
                                </tr>
                                <tr>
                                    <td>Mededeling na afloop seizoen</td>
                                    <td>
                                        <input
                                            type="textarea"
                                            name="announcement"
                                            value={data.announcement}
                                            onChange={(e) => setData('announcement', e.target.value)}
                                        />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div className="btn-group btn-group-lg mr-2" role="group" aria-label="chooser">
                            <button type="submit" className="btn btn-success form-control" disabled={processing}>
                                Pas aan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}
