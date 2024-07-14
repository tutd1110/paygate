<?php

namespace App\Helper;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class PaginateArray
{
    public function paginateArray($items, $perPage, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $current_page_orders = array_slice($items->toArray(), ($page - 1) * $perPage, $perPage);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($current_page_orders, count($items->toArray()), $perPage, $page, $options);
    }
}
