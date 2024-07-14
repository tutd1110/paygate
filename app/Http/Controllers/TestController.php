<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class TestController extends Controller
{
    public function redisConnect(Request $request){
        try{
            $redis = Redis::connection();
            return response('redis working');
        }catch(\Predis\Connection\ConnectionException $e){
            return response('error connection redis');
        }
    }
}
