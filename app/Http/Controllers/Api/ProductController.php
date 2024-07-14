<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\RtaProduct\RtaProductRepository;
use App\Repositories\RtaProduct\RtaProductRepositoryInterface;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    /***
     * @var RtaProductRepositoryInterface | RtaProductRepository
     */
    private $rtaProductRepository;

    public function __construct(RtaProductRepositoryInterface $rtaProductRepository) {
        $this->rtaProductRepository = $rtaProductRepository;
    }


    public function index() {
        $listProducts = $this->rtaProductRepository->getAll();

        return response()->json([
            'code' => '200',
            'message' => 'get success',
            'data' => [
                'products' => $listProducts
            ]
        ]);
    }
}
