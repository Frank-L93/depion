import React, { useState } from 'react';
import { Head, Link, useForm, usePage, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

export default function Games({ rounds, games, users, round_to_process }) {
    const { delete: destroy, processing } = useForm();
    const [expandedRound, setExpandedRound] = useState(null);
    const [editingGame, setEditingGame] = useState(null);
    const [formData, setFormData] = useState({});
    const handleDelete = (id) => {
        if (confirm('Weet je zeker dat je deze partij wilt verwijderen?')) {
            destroy(route('destroyGames', id));
        }
    };

    const toggleRound = (roundId) => {
        setExpandedRound(expandedRound === roundId ? null : roundId);
    };

    const handleEdit = (game) => {
        setEditingGame(game.id);
        setFormData({
            id: game.id,
            white: game.white,
            black: game.black,
            result: game.result,
        });
    };

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData((prev) => ({
            ...prev,
            [name]: value,
        }));
    };

    const handleSave = (gameId) => {
        router.post(`/Admin/Games/${gameId}/update`, formData, {
            onSuccess: () => setEditingGame(null),
        });
    };

    const resultOptions = [
        { value: "1-0", text: '1-0' },
        { value: "0.5-0.5", text: '0.5-0.5' },
        { value: "0-1", text: '0-1' },
        { value: "0-1R", text: '0-1R' },
        { value: "1-0R", text: '1-0R' },
    ];

    const translateBlack = (black) => {
        switch (black) {
            case "Bye":
                return "Bye";
            case "Club":
                return "Afwezig i.v.m. Clubverplichting";
            case "Personal":
                return "Afwezig i.g.v. force majeure";
            case "Other":
            case "Empty":
                return "Afwezig";
            default:
                return users.find((user) => user.id === parseInt(black))?.name || black;
        }
    };

    return (
        <AdminLayout>
            <Head title="Partijen" />
            <div className="card text-black bg-light mb-3">
                <div className="card-header text-center">
                    {round_to_process.id !== 0 && (
                        <a
                            href={`/Admin/RankingList/${round_to_process.id}/calculate`}
                            className="btn btn-sm btn-secondary float-left"
                        >
                            Verwerk Partijen voor ronde {round_to_process.id}
                        </a>
                    )}
                    Partijen
                </div>
                <div className="card-body">
                    {rounds.map((round) => (
                        <div key={round.id} className="card">
                            <div className="card-header" id={`heading${round.id}`}>
                                <button
                                    className="btn btn-link"
                                    onClick={() => toggleRound(round.id)}
                                    aria-expanded={expandedRound === round.id}
                                    aria-controls={`collapse${round.id}`}
                                >
                                    Ronde {round.id}
                                </button>
                                {round.processed !== 1 && (
                                    <Link
                                        href={`/Admin/Match/${round.id}/`}
                                        className="btn btn-sm btn-secondary float-right"
                                    >
                                        Genereer Partijen voor Ronde {round.id}
                                    </Link>
                                )}
                            </div>
                            {expandedRound === round.id && (
                                <div id={`collapse${round.id}`} className="collapse show" aria-labelledby={`heading${round.id}`}>
                                    <div className="card-body">
                                        <table className="table table-hover">
                                            <thead className="thead-dark">
                                                <tr>
                                                    <th>Ronde</th>
                                                    <th>Wit</th>
                                                    <th>Zwart</th>
                                                    <th>Resultaat</th>
                                                    <th>Verwijder</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {games.filter((game) => game.round_id === round.id).map((game) => (
                                                    <tr key={game.id}>
                                                        <td>
                                                            <Link href={`/games/${game.id}`}>
                                                                {game.round_id} - {new Date(round.date).toLocaleDateString('nl-NL', { day: 'numeric', month: 'short', year: 'numeric' })}
                                                            </Link>
                                                        </td>
                                                        <td>
                                                            {editingGame === game.id ? (
                                                                <select
                                                                    name="white"
                                                                    value={formData.white}
                                                                    onChange={handleChange}
                                                                    className="form-control"
                                                                >
                                                                    <option value="">Selecteer speler</option>
                                                                    {users.map((user) => (
                                                                        <option key={user.id} value={user.id}>
                                                                            {user.name}
                                                                        </option>
                                                                    ))}
                                                                    <option value="bye">Bye</option>
                                                                </select>
                                                            ) : (
                                                                <span onClick={() => handleEdit(game)} style={{ color: 'gray', cursor: 'pointer' }}>
                                                                    {users.find((user) => user.id === game.white)?.name || game.white}
                                                                </span>
                                                            )}
                                                        </td>
                                                        <td>
                                                            {editingGame === game.id ? (
                                                                <select
                                                                    name="black"
                                                                    value={formData.black}
                                                                    onChange={handleChange}
                                                                    className="form-control"
                                                                >
                                                                    <option value="">Selecteer speler</option>
                                                                    {users.map((user) => (
                                                                        <option key={user.id} value={user.id}>
                                                                            {user.name}
                                                                        </option>
                                                                    ))}
                                                                    <option value="bye">Bye</option>
                                                                </select>
                                                            ) : (
                                                                <span onClick={() => handleEdit(game)} style={{ color: 'gray', cursor: 'pointer' }}>
                                                                    {translateBlack(game.black)}
                                                                </span>
                                                            )}
                                                        </td>
                                                        <td>
                                                            {editingGame === game.id ? (
                                                                <select
                                                                    name="result"
                                                                    value={formData.result}
                                                                    onChange={handleChange}
                                                                    className="form-control"
                                                                >
                                                                    <option value="">Selecteer resultaat</option>
                                                                    {resultOptions.map((option) => (
                                                                        <option key={option.value} value={option.value}>
                                                                            {option.text}
                                                                        </option>
                                                                    ))}
                                                                </select>
                                                            ) : (
                                                                <span onClick={() => handleEdit(game)} style={{ color: 'gray', cursor: 'pointer' }}>
                                                                    {game.result}
                                                                </span>
                                                            )}
                                                        </td>
                                                        <td>
                                                            {editingGame === game.id ? (
                                                                <button
                                                                    onClick={() => handleSave(game.id)}
                                                                    className="btn btn-sm btn-success"
                                                                    disabled={processing}
                                                                >
                                                                    Opslaan
                                                                </button>
                                                            ) : (
                                                                <button
                                                                    onClick={() => handleDelete(game.id)}
                                                                    className="btn btn-sm btn-danger"
                                                                    disabled={processing}
                                                                >
                                                                    Verwijder
                                                                </button>
                                                            )}
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                        <a href={`/Admin/Game/Add/${round.id}`} className="btn btn-sm btn-secondary">
                                            Voeg partij toe
                                        </a>
                                    </div>
                                </div>
                            )}
                        </div>
                    ))}
                </div>
            </div>
        </AdminLayout>
    );
}
