<?php

namespace Database\Seeders;

use App\Models\ThirdParty;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ThirdPartySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            [
                'id' => 1,
                'name' => 'Đổ contact',
                'key' => 'insert_contact',
                'description' => 'Đổ contact',
                'last_active' => Carbon::now(),
                'token' => '123abcd',
            ]
        ];
        foreach ($items as $item) {
            ThirdParty::updateOrCreate(['id' => $item['id']], $item);
        }

    }
}
