<?php

namespace Database\Seeders;

use App\Helper\RandomHelper;
use App\Models\Gifts\Ticket;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class testRunWheels extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ticketEnd = Ticket::query()->orderBy('id','desc')->first();
        $tickets = [];
        for ($i = 1; $i <= 10000; $i++) {
            array_push($tickets, [
                'id' => $i + ($ticketEnd['id'] ?? 1),
                'landing_page_id' => 104,
                'contact_lead_process_id' => 67777,
                'bill_code' => mt_rand(10000, 99999).'/'.mt_rand(1000, 9900).'/01/BL/BNH',
                'bill_value' => mt_rand(200000, 40000000),
                'store_name' => 'NS FAHASA HÀ NỘI',
                'status'=>'verified',
            ]);
        }
        foreach(array_chunk($tickets, 1000) as $key => $smallerArray) {
            Ticket::insert($smallerArray);
        }
//


    }
}
