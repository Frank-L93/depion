<?php

namespace App\Actions;

use App\Models\Ranking;
use App\Models\Game;
use App\Models\Config;
use App\Models\User;
use App\Models\Round;
use App\Actions\TPRHelper;
use Illuminate\Support\Facades\Log;

class newCalculation
{
    public function Calculate($round)
    {
        $round = (int) $round;
        $firstPart = $this->isFirstSeasonPart($round);
        $seasonBreak = $this->isSeasonBreak();
        $movedToSummer = $this->movedToSummer();
        if($round == $seasonBreak || ($round > $seasonBreak && $movedToSummer == false)){
            // Now we need to move the score to the winterscore as well, as we are moving to the second part of the season, so let's create a variable that we can provide to the UpdateRankings function
            $moveToWinterScore = true;
        }else{
            $moveToWinterScore = false;
        }

        Log::info("Starting calculation for round: $round");

        // Reset rankings
        $this->resetRankings($round, $firstPart, $moveToWinterScore);

        // Process games
        $games = Game::where('round_id', '<=', $round)->get();
        Log::info("Found " . count($games) . " games to process.");

        foreach ($games as $game) {
            $this->processGame($game, $round);
        }

        // Mark the round as processed
        $this->markRoundAsProcessed($round);

        // Update rankings
        $this->updateRankings($moveToWinterScore);

        Log::info("Finished calculation for round: $round");
    }

    private function isFirstSeasonPart($round)
    {
        $lastRoundForSeasonBreak = Config::SeasonPart();
        return $round <= $lastRoundForSeasonBreak;
    }

    private function resetRankings($round, $firstPart, $moveToWinterScore)
    {
        $rankings = Ranking::all();

        foreach ($rankings as $ranking) {

            if($moveToWinterScore){
                $ranking->winterscore = $ranking->score;
                $ranking->winter_amount = $ranking->amount;
                $ranking->winter_gamescore = $ranking->gamescore;
                $ranking->winter_ratop = $ranking->ratop;
            }
            $ranking->round = $round;
            if($firstPart){
                $ranking->score = 0;
            }else{
                $ranking->score = $ranking->winterscore;
            }

            $ranking->amount = 0;
            $ranking->gamescore = 0;
            $ranking->ratop = 0;
            $ranking->save();

            Log::info("Reset ranking for user: {$ranking->user_id}");
        }
    }

    private function processGame($game, $round)
    {
         // Award Presence score if the player is present and does not have an "Afwezigheid" result
    // Award Presence score to both players if they are present and do not have an "Afwezigheid" result
    if ($game->result !== "Afwezigheid") {
        $this->awardPresenceScore($game->white);
        if ($game->black !== "Bye" && $game->black !== "Other") {
            $this->awardPresenceScore($game->black);
        }
    }
        if ($game->result === "Afwezigheid") {
            $this->processAbsence($game, $round);
        } else {
            $this->processResult($game, $round);
        }
    }

    private function processAbsence($game, $round)
    {
        $whiteRanking = $this->getOrCreateRanking($game->white);
        $whiteScore = $this->calculateInitialScore($whiteRanking, $game->white);

        $absenceType = $game->black;
        $whiteScore += $this->calculateAbsenceScore($game, $whiteRanking, $absenceType, $round);

        $whiteRanking->score = $whiteScore;
        $whiteRanking->save();

        Log::info("Processed absence for user: {$game->white} in round: {$game->round_id} with reason: $absenceType");
    }

    private function processResult($game, $round)
    {
        $result = explode("-", $game->result);
        $whiteResult = $result[0];
        $blackResult = $result[1];

        $whiteRanking = $this->getOrCreateRanking($game->white);
        $whiteScore = $this->calculateInitialScore($whiteRanking, $game->white);
        if($game->black === "Bye" || $game->black == "Other"){
            $blackRanking = "No";
            $blackScore = "No";
        }else{
        $blackRanking = $this->getOrCreateRanking($game->black);


        $blackScore = $this->calculateInitialScore($blackRanking, $game->black);
        }
        if ($game->black === "Bye") {
            $whiteScore += $this->calculateByeScore($game, $whiteRanking, $round);
        } else {
            $whiteScore += $this->calculateGameScore($whiteResult, $whiteRanking, $blackRanking, $round);
            $blackScore += $this->calculateGameScore($blackResult, $blackRanking, $whiteRanking, $round);
        }

        $this->updateRankingStats($whiteRanking, $whiteScore, $blackRanking, $blackScore, $whiteResult, $blackResult);

        Log::info("Processed game: {$game->white} vs {$game->black} in round: {$game->round_id} with result: {$game->result}");
    }

    private function calculateAbsenceScore($game, $ranking, $type, $round)
    {
        if ($type === "Club" || $type === "Personal" || $type === "Other") {
            $scoringFactor = Config::Scoring($type);
            return $game->round_id < $round ? $scoringFactor * $ranking->lastvalue : $scoringFactor * $ranking->value;
        }

        return 0;
    }

    private function calculateByeScore($game, $ranking, $round)
    {
        $scoringFactor = Config::Scoring("Bye");
        return $game->round_id < $round ? $scoringFactor * $ranking->lastvalue : $scoringFactor * $ranking->value;
    }

    private function calculateGameScore($result, $ranking, $opponentRanking, $round)
    {
        $scoringFactor = $result === "1" ? 1 : ($result === "0.5" ? 0.5 : 0);
        $opponentValue = $opponentRanking->amount < 5 ? $opponentRanking->firstvalue : $opponentRanking->value;

        // Use the round to determine whether to use lastvalue or value
        return $round < $ranking->round ? $scoringFactor * $opponentRanking->lastvalue : $scoringFactor * $opponentValue;
    }

    private function updateRankingStats($whiteRanking, $whiteScore, $blackRanking, $blackScore, $whiteResult, $blackResult)
    {
        $whiteRanking->score = $whiteScore;
        $whiteRanking->amount += 1;
        $whiteRanking->gamescore += $whiteResult === "1" ? 1 : ($whiteResult === "0.5" ? 0.5 : 0);
        $whiteRanking->tpr = $this->calculateTPR($whiteRanking->user_id);
        $whiteRanking->save();
        if($blackRanking == "No"){

        }else{
            $blackRanking->score = $blackScore;
        $blackRanking->amount += 1;
        $blackRanking->gamescore += $blackResult === "1" ? 1 : ($blackResult === "0.5" ? 0.5 : 0);
        $blackRanking->tpr = $this->calculateTPR($blackRanking->user_id);
        $blackRanking->save();
        }




    }

    private function getOrCreateRanking($userId)
    {
        $ranking = Ranking::firstOrCreate(
            ['user_id' => $userId],
            [
                'score' => 0,
                'value' => Config::InitRanking('start'),
                'firstvalue' => Config::InitRanking('start'),
            ]
        );

        return $ranking;
    }

    private function calculateInitialScore($ranking, $userId)
    {
        $user = User::find($userId);
        return $ranking->score === 0 ? ($user->beschikbaar === 0 ? $ranking->firstvalue : $ranking->value) : $ranking->score;
    }

    private function markRoundAsProcessed($round)
    {
        $roundProcessed = Round::find($round);
        $roundProcessed->processed = 1;
        $roundProcessed->save();

        Log::info("Marked round $round as processed.");
    }

    private function updateRankings($moveToWinterScore)
    {
        $rankings = Ranking::orderBy('score', 'desc')->get();
        $rankingValue = Config::InitRanking("start");

        if($moveToWinterScore){
        // Update the value of Config->summer.
        $config = Config::find(1);
        $config->summer = 1;
        $config->save();
        }

        foreach ($rankings as $ranking) {

            $ranking->lastvalue2 = $ranking->lastvalue;
            $ranking->lastvalue = $ranking->value;
            $ranking->value = $rankingValue;
            $ranking->save();

            Log::info("Updated ranking value for user: {$ranking->user_id} to: {$ranking->value}");
            $rankingValue -= Config::InitRanking("step");
        }
    }

    private function isSeasonBreak(){
        return Config::SeasonPart();
    }

    private function movedToSummer(){
        return Config::Summer();
    }

    // tpr
    public function calculateTPR($player)
    {
        $user = Ranking::where('user_id', $player)->first();
        if (($user->amount + $user->winter_amount) == 0) {
            return 0;
        }

        $divide = ($user->gamescore + $user->winter_gamescore) / ($user->amount + $user->winter_amount);
        $average_rating = ($user->ratop + $user->winter_ratop) / ($user->amount + $user->winter_amount);
        $based_on_divide = $this->GetValueForTPR($divide);
        $tpr = $average_rating + $based_on_divide;
        return $tpr;
    }

    public function GetValueForTPR($amount)
    {
        $amount = round($amount, 2);
        $value = TPRHelper::where('p', $amount)->first();
        if ($value == null) {
            return 0;
        }
        return $value->dp;
    }

    private function awardPresenceScore($playerId)
{
    $ranking = $this->getOrCreateRanking($playerId);

    // Add the Presence score
    $presenceScore = Config::Scoring("Presence");
    $ranking->score += $presenceScore;

    $ranking->save();

    Log::info("Awarded Presence score of $presenceScore to user: $playerId");
}
}