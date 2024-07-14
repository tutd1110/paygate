<?php

namespace App\Http\Controllers\Api\Gifts;

use App\Helper\PaginateArray;
use App\Http\Controllers\Controller;
use App\Models\ContactLeadProcess;
use App\Models\Gifts\RandomGift;
use App\Models\Gifts\RandomGiftContact;
use App\Models\Gifts\Ticket;
use Illuminate\Http\Request;

class RandomGiftContactController extends Controller
{
    public function __construct(RandomGiftContact $randomGiftContact)
    {
        set_time_limit(20000);
        $this->randomGiftContact = $randomGiftContact;
    }

    public function index(Request $request)
    {
        $filter = $request->all();
        $query = $this->randomGiftContact::query();
        if (isset($filter['gift'])) {
            $query = $query->with('gift');;
        }
        if (isset($filter['contact'])) {
            $query = $query->with('contact');
        }
        if (isset($filter['ticket'])) {
            $query = $query->with('ticket');
        }
        if (isset($filter['id'])) {
            $query = $query->where('id', $filter['id']);
        }
        if (isset($filter['landing_page_id'])) {
            if (is_array($filter['landing_page_id'])) {
                $query = $query->whereIn('landing_page_id', $filter['landing_page_id']);
            } else {
                $query = $query->where('landing_page_id', $filter['landing_page_id']);
            }
        }
        if (isset($filter['contact_id'])) {
            if (is_array($filter['contact_id'])) {
                $query = $query->whereIn('contact_id', $filter['contact_id']);
            } else {
                $query = $query->where('contact_id', $filter['contact_id']);
            }
        }
        if (isset($filter['full_name'])) {
            $query = $query->whereHas('contact', function ($query) use ($filter) {
                if (is_array($filter['full_name'])) {
                    $query->whereIn('full_name', 'like', '%'.$filter['full_name'] . '%');
                } else {
                    $query->where('full_name', 'like', '%'.$filter['full_name'] . '%');
                }
            });
        }
        if (isset($filter['phone'])) {
            $query = $query->whereHas('contact', function ($query) use ($filter) {
                if (is_array($filter['phone'])) {
                    $query->whereIn('phone', $filter['phone']);
                } else {
                    $query->where('phone', $filter['phone']);
                }
            });
        }
        if (isset($filter['email'])) {
            $query = $query->whereHas('contact', function ($query) use ($filter) {
                if (is_array($filter['email'])) {
                    $query->whereIn('email', $filter['email']);
                } else {
                    $query->where('email', $filter['email']);
                }
            });
        }
        if (isset($filter['utm_medium'])) {
            $query = $query->whereHas('contact', function ($query) use ($filter) {
                if (is_array($filter['utm_medium'])) {
                    $query->whereIn('utm_medium', $filter['utm_medium']);
                } else {
                    $query->where('utm_medium', $filter['utm_medium']);
                }
            });
        }
        if (isset($filter['utm_source'])) {
            $query = $query->whereHas('contact', function ($query) use ($filter) {
                if (is_array($filter['utm_source'])) {
                    $query->whereIn('utm_source', $filter['utm_source']);
                } else {
                    $query->where('utm_source', $filter['utm_source']);
                }
            });
        }
        if (isset($filter['utm_campaign'])) {
            $query = $query->whereHas('contact', function ($query) use ($filter) {
                if (is_array($filter['utm_campaign'])) {
                    $query->whereIn('utm_campaign', $filter['utm_campaign']);
                } else {
                    $query->where('utm_campaign', $filter['utm_campaign']);
                }
            });
        }
        if (isset($filter['supplier_code'])) {
            $query = $query->whereHas('gift', function ($query) use ($filter) {
                if (is_array($filter['supplier_code'])) {
                    $query->whereIn('supplier_code', $filter['supplier_code']);
                } else {
                    $query->where('supplier_code', $filter['supplier_code']);
                }
            });
        }
        if (isset($filter['bill_code'])) {
            $query = $query->whereHas('ticket', function ($query) use ($filter) {
                if (is_array($filter['bill_code'])) {
                    $query->whereIn('bill_code', $filter['bill_code']);
                } else {
                    $query->where('bill_code', $filter['bill_code']);
                }
            });
        }
        if (isset($filter['ticket_id'])) {
            if (is_array($filter['ticket_id'])) {
                $query = $query->whereIn('ticket_id', $filter['ticket_id']);
            } else {
                $query = $query->where('ticket_id', $filter['ticket_id']);
            }
        }
        if (isset($filter['gift_id'])) {
            if (is_array($filter['gift_id'])) {
                $query = $query->whereIn('gift_id', $filter['gift_id']);
            } else {
                $query = $query->where('gift_id', $filter['gift_id']);
            }
        }
        if (isset($filter['user_id'])) {
            if (is_array($filter['user_id'])) {
                $query = $query->whereIn('user_id', $filter['user_id']);
            } else {
                $query = $query->where('user_id', $filter['user_id']);
            }
        }
        if (isset($filter['start_date'])) {
            $query = $query->where('created_at', '>=', date('Y-m-d H:i:s', strtotime($filter['start_date'])));
        }
        if (isset($filter['end_date'])) {
            $query = $query->where('created_at', '<=', date('Y-m-d 23:59:59', strtotime($filter['end_date'])));
        }
        if (isset($filter['order_by'])) {
            if (is_array($filter['order_by'])) {
                foreach ($filter['order_by'] as $key => $value) {
                    $query = $query->orderBy($value, $filter['direction'][$key] ?? 'asc');
                }
            } else {
                $filter['order_by'] = explode(',', $filter['order_by']);
                $filter['direction'] = explode(',', $filter['direction']);
                foreach ($filter['order_by'] as $key => $value) {
                    $query = $query->orderBy($value, $filter['direction'][$key] ?? 'asc');
                }
            }
        }
        /** Export danh sách quay thưởng */
        if (empty($filter['export'])) {
            $listRandomGiftContact = $query->paginate($filter['limit'] ?? 20);
            return response()->json([
                'status' => true,
                'message' => 'get success',
                'data' => [
                    'listRandomGiftContact' => $listRandomGiftContact,
                ]
            ]);
        } else {
            $listRandomGiftContact = $query->get();
            $listTicketOld = Ticket::select('id', 'bill_code', 'bill_value', 'store_name')->get();
            $listGiftOld = RandomGift::select('id', 'supplier_code', 'name')->get();
            $listContactOld = ContactLeadProcess::select('id', 'full_name', 'email', 'phone', 'utm_medium', 'utm_source', 'utm_campaign');
            if (isset($filter['landing_page_id'])) {
                $listContactOld = $listContactOld->where('landing_page_id', $filter['landing_page_id']);
            }
            $listContactOld = $listContactOld->get();
            $listTicket = [];
            $listGift = [];
            $listContact = [];

            foreach ($listTicketOld as $value) {
                $listTicket[$value->id] = $value;
            }
            foreach ($listGiftOld as $value) {
                $listGift[$value->id] = $value;
            }
            foreach ($listContactOld as $value) {
                $listContact[$value->id] = $value;
            }
            foreach ($listRandomGiftContact as $key => $value) {
                $listRandomGiftContact[$key]['ticket'] = [];
                $listRandomGiftContact[$key]['gift'] = [];
                $listRandomGiftContact[$key]['contact'] = [];
                if (isset($listTicket[$value['ticket_id']])) {
                    $value['ticket'] = $listTicket[$value['ticket_id']];
                }
                if (isset($listGift[$value['gift_id']])) {
                    $value['gift'] = $listGift[$value['gift_id']];
                }
                if (isset($listContact[$value['contact_id']])) {
                    $value['contact'] = $listContact[$value['contact_id']];
                }
            }
            return response()->json([
                'status' => true,
                'message' => 'get success',
                'data' => [
                    'listRandomGiftContact' => $listRandomGiftContact,
                ]
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
