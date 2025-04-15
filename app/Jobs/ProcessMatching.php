<?php

namespace App\Jobs;

use App\Actions\MatchGames;
use App\Models\User;
use App\Notifications\PushDemo;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessMatching implements ShouldQueue
{
    use Queueable;

    protected $round;

    protected $players;

    /**
     * Create a new job instance.
     */
    public function __construct($players, $round)
    {
        $this->players = $players;
        $this->round = $round;
    }

    public function handle(): void
    {
        Log::info('Starting ProcessMatching job for round: '.$this->round);
        try {
            DB::beginTransaction();
            $matching = new MatchGames;
            $result = $matching->InitPairing($this->players, $this->round);

            if ($result == false) {
                Log::error('Error with matching: return false. Round '.$this->round);
                throw new Exception('Error with matching: return false. Round '.$this->round);
            }
            DB::commit();
            Log::info('Successfully finished ProcessMatching for round: '.$this->round);

            // Send the notification
            $this->sendSuccessNotification();

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('ProcessMatching job failed for round: '.$this->round.' - Error: '.$e->getMessage());
            // You could optionally send a notification here,
            // if it fails after the retries it will be added to the failed-jobs.
            $this->fail($e); // Fail this job, so that it will be added to the failed-jobs table
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        // Send user notification, etc...
        Log::error('Failed job: ProcessMatching for round: '.$this->round.'with error: '.$exception);

    }

    private function sendSuccessNotification()
    {
        // Find all admins and competitieleiders
        $users = User::where('rechten', 2)->get();

        // Send the notification to each admin/competitieleider
        foreach ($users as $user) {
            $user->notify(new PushDemo('Partijen voor ronde '.$this->round.' zijn succesvol aangemaakt.', 'Partijen', '3'));
        }
    }
}
