<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveUnverified extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:remove-unverified';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove users who have not verified their email addresses';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Define the time limit for unverified accounts (e.g., 30 days)
        $timeLimit = now()->subDays(30);

        // Delete unverified users
        $deleted = DB::table('users')
            ->whereNull('email_verified_at')
            ->where('created_at', '<', $timeLimit)
            ->delete();

        // Log or output the number of deleted users
        $this->info("Deleted {$deleted} unverified user(s).");
    }
}

