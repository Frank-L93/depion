import React, { useState } from 'react';
import { Head, Link, useForm, usePage, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

export default function Users({ users }) {
    const { auth } = usePage().props;
    const { delete: destroy, processing } = useForm();
    const [editing, setEditing] = useState({});
    const [currentEdit, setCurrentEdit] = useState({});

    const handleDelete = (id) => {
        if (confirm('Weet je zeker dat je deze gebruiker wilt verwijderen?')) {
            destroy(route('destroyUser', id));
        }
    };

    const handleEdit = (id, field, value) => {
        router.post('/Admin/Users/update', { id: id, [field]: value });
    };

    const handleChange = (id, field, value) => {
        setEditing((prev) => ({
            ...prev,
            [id]: {
                ...prev[id],
                [field]: value,
            },
        }));
    };

    const handleBlur = (id, field) => {
        if (editing[id] && editing[id][field] !== undefined) {
            handleEdit(id, field, editing[id][field]);
        }
        setCurrentEdit({});
    };

    const handleFocus = (id, field) => {
        setCurrentEdit({ id, field });
    };

    return (
        <AdminLayout>
            <Head title="Gebruikers" />
            {auth.user.rechten === 2 && (
                <div className="card text-black bg-light mb-3">
                    <div className="card-header text-center">
                        Gebruikers
                        <a className="btn btn-sm btn-secondary float-right" href="/register" role="button">
                            Maak Gebruiker
                        </a>
                    </div>
                    <div className="card-body">
                        <div className="alert alert-info" role="alert">
                            Klik op een veld om het te bewerken. Druk op Enter of klik buiten de velden om de wijzigingen op te slaan.
                        </div>
                        <table className="table table-hover">
                            <thead className="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Naam</th>
                                    <th>E-mail</th>
                                    <th>Rechten</th>
                                    <th>Rating</th>
                                    <th>KNSB ID</th>
                                    <th>Beschikbaar</th>
                                    <th>Verwijder</th>
                                </tr>
                            </thead>
                            <tbody>
                                {users.map((user) => (
                                    <tr key={user.id}>
                                        <td>
                                            <Link href={`/users/${user.id}`}>{user.id}</Link>
                                        </td>
                                        <td onClick={() => handleFocus(user.id, 'name')}>
                                            {currentEdit.id === user.id && currentEdit.field === 'name' ? (
                                                <input
                                                    type="text"
                                                    value={editing[user.id]?.name || user.name}
                                                    onChange={(e) => handleChange(user.id, 'name', e.target.value)}
                                                    onBlur={() => handleBlur(user.id, 'name')}
                                                    autoFocus
                                                />
                                            ) : (
                                                user.name
                                            )}
                                        </td>
                                        <td onClick={() => handleFocus(user.id, 'email')}>
                                            {currentEdit.id === user.id && currentEdit.field === 'email' ? (
                                                <input
                                                    type="text"
                                                    value={editing[user.id]?.email || user.email}
                                                    onChange={(e) => handleChange(user.id, 'email', e.target.value)}
                                                    onBlur={() => handleBlur(user.id, 'email')}
                                                    autoFocus
                                                />
                                            ) : (
                                                user.email
                                            )}
                                        </td>
                                        <td onClick={() => handleFocus(user.id, 'rechten')}>
                                            {currentEdit.id === user.id && currentEdit.field === 'rechten' ? (
                                                <select
                                                    value={editing[user.id]?.rechten || user.rechten}
                                                    onChange={(e) => handleChange(user.id, 'rechten', e.target.value)}
                                                    onBlur={() => handleBlur(user.id, 'rechten')}
                                                    autoFocus
                                                >
                                                    <option value="0">Gebruiker</option>
                                                    <option value="1">Competitieleider</option>
                                                    <option value="2">Admin</option>
                                                </select>
                                            ) : (
                                                user.rechten === 2 ? 'Admin' : user.rechten === 1 ? 'Competitieleider' : 'Gebruiker'
                                            )}
                                        </td>
                                        <td onClick={() => handleFocus(user.id, 'rating')}>
                                            {currentEdit.id === user.id && currentEdit.field === 'rating' ? (
                                                <input
                                                    type="text"
                                                    value={editing[user.id]?.rating || user.rating}
                                                    onChange={(e) => handleChange(user.id, 'rating', e.target.value)}
                                                    onBlur={() => handleBlur(user.id, 'rating')}
                                                    autoFocus
                                                />
                                            ) : (
                                                user.rating
                                            )}
                                        </td>
                                        <td onClick={() => handleFocus(user.id, 'knsb_id')}>
                                            {currentEdit.id === user.id && currentEdit.field === 'knsb_id' ? (
                                                <input
                                                    type="text"
                                                    value={editing[user.id]?.knsb_id || user.knsb_id}
                                                    onChange={(e) => handleChange(user.id, 'knsb_id', e.target.value)}
                                                    onBlur={() => handleBlur(user.id, 'knsb_id')}
                                                    autoFocus
                                                />
                                            ) : (
                                                user.knsb_id
                                            )}
                                        </td>
                                        <td onClick={() => handleFocus(user.id, 'beschikbaar')}>
                                            {currentEdit.id === user.id && currentEdit.field === 'beschikbaar' ? (
                                                <select
                                                    value={editing[user.id]?.beschikbaar || user.beschikbaar}
                                                    onChange={(e) => handleChange(user.id, 'beschikbaar', e.target.value)}
                                                    onBlur={() => handleBlur(user.id, 'beschikbaar')}
                                                    autoFocus
                                                >
                                                    <option value="1">Standaard Beschikbaar</option>
                                                    <option value="0">Standaard Niet Beschikbaar</option>
                                                </select>
                                            ) : (
                                                user.beschikbaar === 1 ? 'Standaard Beschikbaar' : 'Standaard Niet Beschikbaar'
                                            )}
                                        </td>
                                        <td>
                                            <button
                                                onClick={() => handleDelete(user.id)}
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
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
