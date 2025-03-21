import { router } from "@inertiajs/react";
export default function RankingModal ({rank, games}) {

    const onClose = () => {
        router.get("/rankings");
    }

    const gamesArray = Object.values(games);
    return (
        <div className="modal fade show" style={{ display: 'block' }} tabIndex="-1" role="dialog" aria-labelledby="rankingModalTitle" aria-hidden="true">
            <div className="modal-dialog modal-dialog-centered" role="document">
                <div className="modal-content">
                    <div className="modal-header">
                        <h5 className="modal-title" id="rankingModalTitle">{rank.user.name}</h5>
                        <hr />
                        <p>Totale score: {rank.score}</p>
                        <p>Score in eerste helft: {rank.winterscore}</p>
                        <p>Waarde in ronde:  {rank.value} <br /> Waarde voor ronde: {rank.lastvalue}</p>
                        <button type="button" onClick={onClose} className="close" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div className="modal-body">
                        <table className="table table-hover">
                            <thead className="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Wit</th>
                                    <th>Zwart</th>
                                    <th>Resultaat</th>
                                    <th>Ronde</th>
                                    <th>Score</th>
                                </tr>
                            </thead>
                            <tbody>
                            {gamesArray.map((game, index) => (
                             <tr key={game.id}>
                                    <td>{index + 1}</td>
                                    <td>{game.white}</td>
                                    <td>{game.black}</td>
                                    <td>{game.result}</td>
                                    <td>{game.round_id}</td>
                                    <td>{game.score.toLocaleString('nl-NL', {
                                                            minimumFractionDigits: 0,
                                                            maximumFractionDigits: 2,
                                                        })}</td>
                                </tr>
                                 ))}
                            </tbody>
                        </table>
                    </div>
                    <div className="modal-footer">
                        <button type="button" className="btn btn-secondary" onClick={onClose}>Sluit</button>
                    </div>
                </div>
            </div>
        </div>
    );
};
