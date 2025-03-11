import React from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

export default function Rankings({ ranking }) {
    const { auth } = usePage().props;
    const { delete: destroy, processing } = useForm();

    const handleDelete = (id) => {
        if (confirm('Weet je zeker dat je deze ranglijst wilt resetten?')) {
            destroy(route('resetRankingList', id));
        }
    };

    return (
        <AdminLayout>
            <Head title="Ranglijst" />
            {auth.user && (
                <div className="card text-black bg-light mb-3">
                    <div className="card-header text-center">
                        Ranglijst
                        <div>
                            {ranking.length > 0 ? (
                                <>
                                    <a className="btn btn-sm btn-danger float-left mx-2" href="/Admin/RankingList/reset" role="button">
                                        Reset de ranglijst
                                    </a>
                                    <a className="btn btn-sm btn-primary float-left mx-2" href="/Admin/RankingList/add" role="button">
                                        Voeg iemand toe
                                    </a>
                                    <a className="btn btn-sm btn-secondary float-right" href="/Admin/RankingList/back" role="button">
                                        Zet de ranglijst een ronde terug
                                    </a>
                                </>
                            ) : (
                                <a className="btn btn-sm btn-secondary float-right" href="/Admin/RankingList/create" role="button">
                                    Genereer Ranglijst
                                </a>
                            )}
                        </div>
                    </div>
                    <div className="card-body">
                        <table className="table table-hover">
                            <thead className="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Naam</th>
                                    <th>Score</th>
                                    <th>Waarde</th>
                                    <th>Pas aan</th>
                                </tr>
                            </thead>
                            <tbody>
                                {ranking.map((rank, index) => (
                                    <tr key={rank.id}>
                                        <td>
                                            <a href={`/Admin/RankingList/${rank.id}`}>{index + 1}</a>
                                        </td>
                                        <td>{rank.user.name}</td>
                                        <td>{rank.score}</td>
                                        <td>{rank.value}</td>
                                        <td>
                                            <a href={`/Admin/RankingList/${rank.id}`} className="btn btn-sm btn-info">
                                                <img src="/assets/icons/pencil.svg" alt="" width="24" height="24" />
                                            </a>
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
