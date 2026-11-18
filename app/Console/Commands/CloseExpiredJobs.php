<?php

namespace App\Console\Commands;

use App\Enums\JobStatus;
use App\Models\Job;
use Illuminate\Console\Command;

class CloseExpiredJobs extends Command
{
    protected $signature = 'cms:close-expired-jobs';

    protected $description = 'Close vacancies whose closing date has passed';

    public function handle(): int
    {
        $closed = Job::query()
            ->where('status', JobStatus::Open)
            ->whereNotNull('closing_at')
            ->where('closing_at', '<', now())
            ->update(['status' => JobStatus::Closed]);

        $this->info("Closed {$closed} expired vacancy(ies).");

        return self::SUCCESS;
    }
}
