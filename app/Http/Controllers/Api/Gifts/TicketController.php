<?php

namespace App\Http\Controllers\Api\Gifts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gifts\TicketRequest;
use App\Jobs\SendEmailHocMai;
use App\Models\EmailSave;
use App\Models\EmailTemplates;
use App\Models\Gifts\RandomGiftContact;
use App\Models\Gifts\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    public function __construct(Ticket $ticket, EmailSave $emailSave)
    {
        set_time_limit(2000);
        $this->ticket = $ticket;
        $this->mail = $emailSave;
    }

    public function index(Request $request)
    {

        $filter = $request->all();
        $query = $this->ticket::query()->with('contact');
        if (isset($filter['id'])) {
            if (is_array($filter['id'])) {
                $query = $query->whereIn('id', $filter['id']);
            } else {
                $query = $query->where('id', $filter['id']);
            }
        }
        if (isset($filter['bill_code'])) {
            if (is_array($filter['bill_code'])) {
                $query = $query->whereIn('bill_code', $filter['bill_code']);
            } else {
                $query = $query->where('bill_code', $filter['bill_code']);
            }
        }
        if (isset($filter['contact_lead_process_id'])) {
            if (is_array($filter['contact_lead_process_id'])) {
                $query = $query->whereIn('contact_lead_process_id', $filter['contact_lead_process_id']);
            } else {
                $query = $query->where('contact_lead_process_id', $filter['contact_lead_process_id']);
            }
        }

        if (isset($filter['store_name'])) {
            $query = $query->where('store_name', $filter['store_name']);
        }
        if (isset($filter['status'])) {
            $query = $query->where('status', $filter['status']);
        }
        if (isset($filter['lock'])) {
            $query = $query->where('lock', $filter['lock']);
        }
        if (isset($filter['get_all'])) {
            $listTicket = $query->get();
        } else {
            $listTicket = $query
                ->paginate($request->get('limit', config('cms.limit')));
        }
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => [
                'ticket' => $listTicket,
            ]
        ]);
    }

    public function store(TicketRequest $request)
    {

        $filter = $request->validated();
        DB::beginTransaction();
        try {
            $tickets = $this->ticket->create($filter);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => [
                    'ticket' => $tickets,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'message' => $e->getMessage(),
                    'type' => 'error',
                ]
            ]);
        }
    }

    public function update(TicketRequest $request, $id)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $ticket = $this->ticket::find($id);
            $ticket->fill($data);
            $ticket->save();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'update success',
                'data' => [
                    'ticket' => $ticket
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'message' => $e->getMessage(),
                    'type' => 'error',
                ]
            ]);
        }
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $tiket = $this->ticket::find($id);
            $tiket->delete();
            DB::commit();
            return response()->json([
                'message' => 'delete success',
                'data' => [
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'message' => $e->getMessage(),
                    'type' => 'error',
                ]
            ]);
        }
    }
}
