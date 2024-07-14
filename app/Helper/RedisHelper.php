<?php

namespace App\Helper;

use Illuminate\Support\Facades\Redis;

class RedisHelper {

    private static $redis;

    public static function rpush($key, $value) {
        if (!isset(self::$redis)) {
            self::$redis = Redis::connection();
        }
        return self::$redis->rpush($key, $value);
    }

    public static function lpop($key) {
        if (!isset(self::$redis)) {
            self::$redis = Redis::connection();
        }
        return self::$redis->lpop($key);
    }

    public function lrange($key,$start = 0,$end = -1){
        if (!isset(self::$redis)) {
            self::$redis = Redis::connection();
        }
        return self::$redis->lrange($key,$start,$end);
    }

    public static function exists($key) {
        $value = Redis::get($key);
        return isset($value);
    }

    public static function set($key, $value, $expire = null) {
        Redis::set($key, $value);
        if (!empty($expire)){
        Redis::expire($key, $expire);
        }
    }

    public static function get($key){
        return Redis::get($key);
    }

    public static function delete($key){
        Redis::del($key);
    }

    public static function hset($key,$field,$value){
        return Redis::hset($key,$field,$value);
    }

    public static function hget($key,$field){
        return Redis::hget($key,$field);
    }

}
