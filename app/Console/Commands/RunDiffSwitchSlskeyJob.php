<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\DiffSwitchSlskeyJob;

class RunDiffSwitchSlskeyJob extends Command
{
    /**
     * The name and signature of the console command.
     * Called by the command:
     * php artisan app:run-diff-switch-slskey-job {switchGroupId} {slskeyCode}
     *
     * @var string
     */
    protected $signature = 'app:run-diff-switch-slskey-job {switchGroupId} {slskeyCode} {createUsers?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the DiffSwitchSlskeyJob to compare the members of a Switch Group with the members of a Slskey Group.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $switchGroupId = $this->argument('switchGroupId');
        $slskeyCode = $this->argument('slskeyCode');
        $createUsers = $this->argument('createUsers');

        echo "Switch Group ID: $switchGroupId\n";
        echo "Slskey Code: $slskeyCode\n";
        echo "\n";
        echo "Starting job...\n";
        echo "Results will be stored in the storage/diff directory.\n";

        $job = DiffSwitchSlskeyJob::dispatch($switchGroupId, $slskeyCode, $createUsers)->onConnection('redis_import_job');
    }
}
