<?php

namespace App\Jobs;

use App\Actions\newCalculation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessCalculation implements ShouldQueue
{
    use Queueable;

    protected $round;
    /**
     * Create a new job instance.
     */
    public function __construct($round)
    {
        $this->round = $round;

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $Calculation = new newCalculation();
        $Calculation->calculate($this->round);
    }
}
