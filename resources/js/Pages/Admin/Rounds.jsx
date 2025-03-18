import React from 'react';
import { Head, router, Link, useForm, usePage } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

export default function Rounds({ rounds }) {
    const { delete: destroy, processing } = useForm();

    const handleDelete = (id) => {
        if (confirm('Weet je zeker dat je deze ronde wilt verwijderen?')) {
            router.delete(route('destroyRounds', id));
        }
    };

    return (
        <AdminLayout>
            <Head title="Rondes" />
            <div className="card text-black bg-light mb-3">
                <div className="card-header text-center">
                    Rondes
                    <a href="/Admin/Rounds/create" className="btn btn-sm btn-secondary float-right">
                        Creeër ronde
                    </a>
                </div>
                <div className="card-body">
                    {rounds.length > 0 ? (
                        <table className="table table-hover">
                            <thead className="thead-dark">
                                <tr>
                                    <th>Ronde</th>
                                    <th>Datum</th>
                                    <th>Publiceer</th>
                                    <th>Publiceer</th>
                                    <th>Pas aan</th>
                                    <th>Verwijder</th>
                                </tr>
                            </thead>
                            <tbody>
                                {rounds.map((round) => (
                                    <tr key={round.id}>
                                        <td>
                                            <a href={`/rounds/${round.id}`}>{round.round}</a>
                                        </td>
                                        <td>{new Date(round.date).toLocaleDateString('nl-NL', { day: 'numeric', month: 'short', year: 'numeric' })}</td>
                                        <td>
                                            {round.published === 0 && (
                                                <a href={`/rounds/${round.id}/games`} className="btn btn-sm btn-info">
                                                    Partijen
                                                </a>
                                            )}
                                        </td>
                                        <td>
                                            {round.ranking === 0 && (
                                                <a href={`/rounds/${round.id}/rankings`} className="btn btn-sm btn-info">
                                                    Ranking
                                                </a>
                                            )}
                                        </td>
                                        <td>
                                            <a href={`/rounds/${round.id}/edit`} className="btn btn-sm btn-info">
                                                <img src="/assets/icons/pencil.svg" alt="" width="24" height="24" />
                                            </a>
                                        </td>
                                        <td>
                                            <button
                                                onClick={() => handleDelete(round.id)}
                                                className="btn btn-sm btn-danger"
                                                disabled={processing}
                                            >
                                                Verwijder
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    ) : (
                        <p>Geen rondes gevonden.</p>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}
