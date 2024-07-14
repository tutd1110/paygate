<?php

namespace App\Console\Commands;

use App\Lib\FormatPhoneNumber;
use Illuminate\Console\Command;

class TestPhpFunction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:php';

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
        dd(FormatPhoneNumber::toBasic('+84   966.056.332'));
        return 0;
    }
}
