import { Head, usePage, router, Link } from '@inertiajs/react';
import Layout from '@/Layouts/Layout';
import React, { useState, useEffect } from 'react';


export default function index({ranking, currentRound}) {
    const props = usePage().props;
    const calculateTwo = (a, b) => {
        a = a * 1;
        b = b * 1;
        return a + b;
    }
    return (
        <Layout>
                    <Head title="Ranglijst" />
                    <div className="card">
                        <div className="card-header text-center">Ranglijst na ronde {currentRound === "Niet" ? '' : currentRound }</div>
                        {currentRound === "Niet" ? (
                <div className="card-body">
                    Ranglijst nog niet gepubliceerd
                </div>
            ) : (
                <div className="card-body">
                    <table className="table table-hover">
                        <thead className="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Naam</th>
                                <th>Score</th>
                                <th>Waarde</th>

                                { props.settings === "1" ? (
                                    <><th>Gespeelde Partijen</th><th>Resultaat</th><th>Partijscore</th><th>TPR</th></>
                                 ) : (
                                    <></>
                                 )}
                            </tr>
                        </thead>
                        <tbody>
                            {ranking.map((rank, index) => (
                                <tr key={rank.id}>
                                    <td>
                                        <Link href={"rankings/" + rank.user.id} className="badge badge-pill badge-info">
                                            {index + 1}
                                        </Link>
                                    </td>
                                    <td>{rank.user.name}</td>
                                    <td>{rank.score.toLocaleString('nl-NL', {minimumFractionDigits: 0, maximumFractionDigits: 2})}</td>
                                    <td>{rank.value}</td>

                                    {props.settings === "1" ? ( <>
                <td>{calculateTwo(rank.amount, rank.winter_amount)}</td>
                <td>{calculateTwo(rank.gamescore, rank.winter_gamescore)}</td>
		<td>{rank.amount > 0 || rank.winter_amount > 0 ? ( (calculateTwo(rank.gamescore, rank.winter_gamescore)) / (calculateTwo(rank.amount, rank.winter_amount)) * 100).toFixed(2) : (<></>)}</td>
                <td>{rank.tpr !== null ? (
                                                        (rank.tpr * 1).toFixed(0)
                                                    ) : (
                                                        "" // Display an empty string if tpr is null
                                                    )}</td>
                </>) : (
                <></>)}
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}

        </div>
       </Layout>
    );
}
