import React, { useState, useEffect } from 'react';
import { Head, Link, useForm, usePage, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Pagination from '@/Pages/Generic/pagination';

export default function Presences({ presences, search: initialSearch }) {
    const { delete: destroy, processing } = useForm();
    const [search, setSearch] = useState(initialSearch || '');

    const handleDelete = (id) => {
        if (confirm('Weet je zeker dat je deze aanwezigheid wilt verwijderen?')) {
            router.delete('/Admin/'+id+'/Presences', {id: id, onSuccess: "Gelukt"})
        }
    };

    useEffect(() => {
        const delayDebounceFn = setTimeout(() => {
            router.get('/Admin/Presences', { search }, { preserveState: true, only: ['presences'] });
        }, 300);

        return () => clearTimeout(delayDebounceFn);
    }, [search]);

    return (
        <AdminLayout>
            <Head title="Aanwezigheid" />
            <div className="card text-black bg-light mb-3">
                <div className="card-header text-center">
                    <Link className="btn btn-sm btn-secondary float-left" href="/Admin/Presences/create" role="button">
                        Genereer Aanwezigheden
                    </Link>
                    Aanwezigheid
                </div>
                <div className="card-body">
                    <div className="mb-3">
                        <input
                            type="text"
                            className="form-control"
                            placeholder="Zoek op naam"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                        />
                    </div>
                    {presences.data.length > 0 ? (
                        <>
                            <table className="table table-hover" id="presencesTable">
                                <thead className="thead-dark">
                                    <tr>
                                        <th>Naam</th>
                                        <th>Ronde</th>
                                        <th>Aanwezig</th>
                                        <th>Verwijder</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {presences.data.map((presence) => (
                                        <tr key={presence.id}>
                                            <td>
                                                <Link href={`/presences/${presence.id}/edit`}>{presence.user.name}</Link>
                                            </td>
                                            <td>{presence.round}</td>
                                            <td><span className={presence.presence === 1 ? 'text-success' : 'text-danger'}>
                                                    {presence.presence === 1 ? 'Aanwezig' : 'Afwezig'}
                                                </span></td>
                                            <td>
                                                <button
                                                    onClick={() => handleDelete(presence.id)}
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
                            <Pagination links={presences.links} search={search} />
                            <Link href="/Admin/Presence/Add" className="btn btn-sm btn-secondary">
                                Voeg aanwezigheid toe
                            </Link>
                        </>
                    ) : (
                        <p>Geen aanwezigheden gevonden.</p>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}
