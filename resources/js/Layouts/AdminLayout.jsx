import React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import Layout from '@/Layouts/Layout';

export default function AdminLayout({children}) {
    const route = usePage().url;
    const props = usePage().props;
    console.log(route);
    return (
       <Layout>
            <div className="card-group">
                <div className="card text-black bg-light mb-3">
                    <div className="card-header">
                        Admin Dashboard van {props.user}
                    </div>
                    <div className="card-body">
                        {!props.user ? (
                            <p>Login om gebruik te maken van het Dashboard.</p>
                        ) : (
                            <>
                                <p>Je kunt hieronder gebruik maken van de verschillende Adminpagina's.</p>
                                <div className="card-group">
                                    <div className="card text-black bg-warning mb-3" style={{ maxWidth: '25em' }}>
                                        <div className="card-header text-center">Clubavond</div>
                                        <div className="card-body">
                                            <p>De volgende stappen moeten worden uitgevoerd om een clubavond af te handelen:</p>
                                            <ul>
                                                <li>Genereer partijen voor aanwezigen op basis van stand</li>
                                                <li>Vul scores in</li>
                                                <li>Bereken stand</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div className="card text-black bg-light mb-3" style={{ maxWidth: '25em' }}>
                                        <div className="card-header text-center">Seizoen</div>
                                        <div className="card-body">
                                            <p>De volgende stappen moeten worden uitgevoerd om een nieuw seizoen op te starten:</p>
                                            <ul>
                                                <li>Stel Seizoeneinde in op 1 onder Configuratie</li>
                                                <li>Reset via knop die hieronder verschijnt</li>
                                                <li>Verwijder eventuele gebruikers uit de gebruikerslijst</li>
                                                <li>Laad nieuwe Ratinglijst</li>
                                                <li>Genereer Ranglijst</li>
                                                <li>Creëer nieuwe rondes</li>
                                                <li>Genereer aanwezigheden</li>
                                                <li>Pas eventuele waarden aan</li>
                                            </ul>
                                            {props.configs.map((config) => (
                                                config.EndSeason === "0" ? null : (
                                                    <Link href="/Admin/Reset" className="btn btn-secondary btn-xs pull-left" key={config.id}>
                                                        Reset seizoen
                                                    </Link>
                                                )
                                            ))}
                                        </div>
                                    </div>
                                </div>
                            </>
                        )}
                    </div>
                </div>
            </div>
            <ul className="nav nav-tabs" role="tablist">
                <li className="nav-item">
                    {route === '/Admin' ? (
                        <a className="nav-link active" href="/Admin">Ratinglijst</a>
                    ) : (
                        <a className="nav-link" href="/Admin">Ratinglijst</a>
                    )}
                </li>
                <li className="nav-item">
                    {route === '/Admin/Config' ? (
                        <Link className="nav-link active" href="/Admin/Config">Configuratie</Link>
                    ) : (
                        <Link className="nav-link" href="/Admin/Config">Configuratie</Link>
                    )}

                </li>
                <li className="nav-item">
                    {route === '/Admin/Users' ? (
                        <Link className="nav-link active" href="/Admin/Users">Gebruikers</Link>
                    ) : (
                        <Link className="nav-link" href="/Admin/Users">Gebruikers</Link>
                    )}

                </li>
                <li className="nav-item">
                    {route === '/Admin/Presences' ? (
                        <Link className="nav-link active" href="/Admin/Presences">Aanwezigheden</Link>
                    ) : (
                        <Link className="nav-link" href="/Admin/Presences">Aanwezigheden</Link>
                    )}

                </li>
                <li className="nav-item">
                    {route === '/Admin/Rankings' ? (
                        <Link className="nav-link active" href="/Admin/Rankings">Ranglijst</Link>
                    ) : (
                        <Link className="nav-link" href="/Admin/Rankings">Ranglijst</Link>
                    )}

                </li>
                <li className="nav-item">
                    {route === '/Admin/rounds' ? (
                        <Link className="nav-link active" href="/Admin/Rounds">Rondes</Link>
                    ) : (
                        <Link className="nav-link" href="/Admin/Rounds">Rondes</Link>
                    )}

                </li>
                <li className="nav-item">
                    {route === '/Admin/games' ? (
                        <Link className="nav-link active" href="/Admin/Games">Partijen</Link>
                    ) : (
                        <Link className="nav-link" href="/Admin/Games">Partijen</Link>
                    )}
                </li>
            </ul>
            <div className="tab-content">
                <br />
                <div>
                    {children}
                </div>
            </div>
       </Layout>
    );
}
