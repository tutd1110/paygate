<?php

namespace App\Console\Commands;

use App\Models\Gifts\RandomGiftContact;
use App\Repositories\UserBuy\UserBuyRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TestContact extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:contact';

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
    public function __construct(UserBuyRepositoryInterface $buyRepository)
    {
        $this->buyRepository = $buyRepository;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $listLog = RandomGiftContact::all();

        foreach ($listLog as $item) {
            $buyData = $this->buyRepository->getPackageComboPackageInTime($item->user_id,
                Carbon::createFromFormat('Y-m-d H:i:s', '2022-04-18 00:00:00')->timestamp,
                Carbon::createFromFormat('Y-m-d H:i:s', '2022-05-06 00:00:00')->timestamp);


            /***
             * trường hợp có gói tặng trong thời gian
             */
            $hasGift = false;
            /***
             * trường hợp đã mua gói trong thời gian
             */
            $hasBuy = false;

            foreach ($buyData->data as $eachCheck) {
                if ($eachCheck) {
                    if ($eachCheck->gift) {
                        $hasGift = true;
                    } else {
                        $hasBuy = true;
                    }
                }
            }

            if ($hasGift || $hasBuy) {
                $this->info('yes '. $item->user_id);
            } else {
                $this->info('no '. $item->user_id);
            }
        }


        return 0;
    }
}
