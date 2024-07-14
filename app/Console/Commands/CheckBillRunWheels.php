<?php

namespace App\Console\Commands;

use App\Helper\Request;
use App\Models\Gifts\Ticket;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckBillRunWheels extends Command
{
    const MAX_SCAN_TICKET = 5;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CheckBillRunWheels';

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
        set_time_limit(20000);
        $this->url_check_ticket = env('URL_CHECK_TICKET', 'https://gw-intservices.fahasa.com/billvalidation/orders');
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('[' . Carbon::now()->format('Y-m-d H:i:s') . '] Start Run: ' . self::class . '');
        $this->scanCheckTicket();
        $this->info('[' . Carbon::now()->format('Y-m-d H:i:s') . '] Run Success: ' . self::class);
        return true;
    }

    public function scanCheckTicket()
    {
        try {
            DB::beginTransaction();
            $listTickets = Ticket::query()
                ->where('scan', Ticket::NO_SCAN)
                ->where('scan_number', '=', 0)
                ->where('status', Ticket::STATUS_APPROVED)
                ->where('created_at', '<=', Carbon::now()->subMinute(5))
                ->limit(100);
            $ticketProcessing = $listTickets->get();
            $tickets = [];
            /** Gửi các ticket chưa được quét để kiểm tra mã bill_code*/
            if (empty(count($ticketProcessing))) {
                return false;
            }

            foreach ($ticketProcessing->toArray() as $key => $ticket) {
                $tickets[] = [
                    'documentno' => $ticket['bill_code'],
                    'amount' => $ticket['bill_value']
                ];
            }
            try {
                $response = Request::post($this->url_check_ticket, [
                    'json' => [
                        'orders' => $tickets
                    ]
                ]);

                $result = json_decode((string)$response->getBody(), true);

                if (!empty(json_last_error())) {
                    $result = [
                        'statusCode' => '500',
                        'message' =>  json_encode(['content' => (string)$response->getBody()]) ,
                    ];
                }
            } catch (\Exception $e) {
                $result = [
                    'statusCode' => $e->getCode(),
                    'message' => $e->getMessage()
                ];
            }

            /** Cập nhật ticket sau khi xử lí */
            $this->updateTicket($ticketProcessing, $result);
            DB::commit();

            /* end job */
            echo json_encode([
                'time'   => date('Y-m-d H:i:s'),
                'ticket' => $tickets,
                'result' => $result
            ]); exit();

        } catch (\Exception $exception) {
            DB::rollBack();

            /* end job */
            echo json_encode([
                'time'   => date('Y-m-d H:i:s'),
                'result' => [
                    'statusCode' => $exception->getCode(),
                    'message'    => $exception->getMessage()
                ]
            ]); exit();
        }
    }

    public function updateTicket($ticketProcessing, $result)
    {
        if (!empty($result['status']) && $result['status'] == 'Success') {
            foreach ($result['data'] as $key => $value) {
                $ticketProcessing[$key]['scan_number'] += 1;
                $ticketProcessing[$key]['response'] = json_encode(['status' => 'true', 'data' => json_encode($value)]);
                if ($value['message'] != Ticket::BILL_CODE_VALID && $ticketProcessing[$key]['scan_number'] == self::MAX_SCAN_TICKET) {
                    $ticketProcessing[$key]['lock'] = Ticket::NO_LOCK;
                    $ticketProcessing[$key]['scan'] = Ticket::YES_SCAN;
                } elseif ($value['message'] == Ticket::BILL_CODE_VALID) {
                    $ticketProcessing[$key]['lock'] = Ticket::YES_LOCK;
                    $ticketProcessing[$key]['scan'] = Ticket::YES_SCAN;
                }
            }
        } else {
            foreach ($ticketProcessing as $key => $value) {
                $ticketProcessing[$key]['scan_number'] += 1;
                $ticketProcessing[$key]['response'] = json_encode(['status' => 'false', 'data' => $result]);
                if ($value['message'] != Ticket::BILL_CODE_VALID && $ticketProcessing[$key]['scan_number'] == self::MAX_SCAN_TICKET) {
                    $ticketProcessing[$key]['lock'] = Ticket::NO_LOCK;
                    $ticketProcessing[$key]['scan'] = Ticket::YES_SCAN;
                }
            }
        }
        foreach ($ticketProcessing as $key => $value) {
            $value->save();
        }
    }
}
