<?php

namespace App\Console\Commands;

use App\Models\LogClientRequest;
use App\Models\RequestLog;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ClearLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:log';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $delete = LogClientRequest::where('created_at', '<=', Carbon::now()->subDays(90))->delete();
        $deleteApiLog = RequestLog::where('created_at', '<=', Carbon::now()->subDays(90))->delete();

        return true;
    }
}
