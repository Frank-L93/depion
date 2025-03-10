import { Head, usePage, router, Link } from '@inertiajs/react';
import Layout from '@/Layouts/Layout';
import React, { useState, useEffect } from 'react';


export default function index({ranking, currentRound}) {
    const props = usePage().props;

    return (
        <Layout>
                    <Head title="Ranglijst" />
                    <div class="card">
                        <div class="card-header text-center">Ranglijst na ronde {currentRound === "Niet" ? '' : currentRound }</div>
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
                                    <td>{rank.score.toFixed(2)}</td>
                                    <td>{rank.value}</td>

                                    {props.settings === "1" ? ( <>
                <td>{rank.amount}</td>
                <td>{rank.gamescore}</td>
		<td>{rank.amount > 0 ? (rank.gamescore / rank.amount * 100).toFixed(2) : (<></>)}</td>
                <td>{rank.tpr !== null ? ( <span>{rank.tpr.toFixed(0)} </span>) : (<span>0</span>)} </td>
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
