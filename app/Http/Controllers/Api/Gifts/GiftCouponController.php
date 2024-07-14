<?php

namespace App\Http\Controllers\Api\Gifts;

use App\Http\Controllers\Controller;
use App\Models\Gifts\GiftCoupon;
use Illuminate\Http\Request;

class GiftCouponController extends Controller
{
    public function __construct(GiftCoupon $giftCoupon)
    {
        $this->giftCoupon = $giftCoupon;
    }
    public function index(Request $request)
    {
        $filter = $request->all();
        $query = $this->giftCoupon::query()->with('randomGift');

        if (isset($filter['id'])) {
            if (is_array($filter['id'])) {
                $query = $query->whereIn('id', $filter['id']);
            } else {
                $query = $query->where('id', $filter['id']);
            }
        }
        if (isset($filter['gift_id'])) {
            if (is_array($filter['gift_id'])) {
                $query = $query->whereIn('gift_id', $filter['gift_id']);
            } else {
                $query = $query->where('gift_id', $filter['gift_id']);
            }
        }
        if (isset($filter['get_all'])) {
            $listGiftCoupon = $query->get();
        } else {
            $listGiftCoupon = $query
                ->paginate($request->get('limit', config('cms.limit')));
        }
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => [
                'giftCoupon' => $listGiftCoupon,
            ]
        ]);
    }

}
