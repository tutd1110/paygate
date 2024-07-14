<?php

namespace Database\Seeders;

use App\Models\Traffic;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class traffics extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        for ($i = 1; $i <= 100000; $i++) {
//            Traffic::create([
//                'landing_page_id' => rand(1, 10000),
//                'user_id' => rand(1, 10000),
//                'campaign_id' => rand(1, 10000),
//                'cookie_id' => Str::uuid(),
//                'session_id' => Str::uuid(),
//                'uri' => \Faker\Provider\Internet::freeEmailDomain(),
//                'query_string'=> \Faker\Provider\Internet::freeEmailDomain(),
//                'utm_medium' => Str::random('12'),
//                'utm_source' => Str::random('12'),
//                'utm_campaign' => Str::random('12'),
//                'register_ip' =>\Faker\Provider\Internet::localIpv4(),
//            ]);
//        }
    }
}
