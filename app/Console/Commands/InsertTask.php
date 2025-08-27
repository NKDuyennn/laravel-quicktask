<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;

class InsertTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Task::create([
            'name' => 'New Task'. time(),
            'user_id' => 1,
        ]);
    }
}
