<?php

namespace App\Console\Commands;

use App\Models\ThirdParty;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ThirdPartyToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'third_party:token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'remake command';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
//
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $listThirdParties = ThirdParty::all();

        $name = $listThirdParties->implode('key', '/');

        $name = $this->ask('input your key: ('.$name.')');

        $t = ThirdParty::where('key', $name)->first();

        if (!$t) {
            $this->error('third party not exist');
            return;
        }
        $t->token = Str::random();
        $t->save();

        $token = auth('third_party')->claims(['token' => $t->token])->login($t);

        $this->line("your name:");
        $this->info($t->name);
        $this->line( "your token: ");
        $this->info($token);
        $this->line("copy token and send to your partner!!");

        return 0;
    }
}
