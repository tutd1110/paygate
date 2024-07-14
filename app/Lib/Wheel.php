<?php

namespace App\Lib;

use Illuminate\Support\Str;

class Wheel
{

    /****
     * @var $listGift Array {
     *                      id: id quà
     *                      name: Tên quà,
     *                      quantity: Số lượng quà còn lại,
     *
     * }
     */
    private $listGift;
    private $defaultGift = null;
    private $forceDefault;

    public function __construct($listGift = [], $defaultGift = null, $forceDefault = false)
    {
        $this->listGift = $listGift;
        $this->defaultGift = $defaultGift;
        $this->forceDefault = $forceDefault;
    }

    public function forceDefault(bool $forceDefault = false) {
        $this->forceDefault = $forceDefault;
    }

    public function getResult()
    {
        $giftResult = null;
        $sumRatio = 0;
        $startCount = 0;
        $listGiftWheel = [];

        if ($this->forceDefault) {
            return $this->defaultGift;
        }

        /****
         * tính ratio của quà
         */
        foreach ($this->listGift as $eachGift) {

            if ($eachGift['quantity'] > 0) {
                $sumRatio += $eachGift['quantity'];
                $listGiftWheel[] = [
                    'name' => $eachGift['name'],
                    'id' => $eachGift['id'],
                    'quantity' => $eachGift['quantity'],
                    'start' => $startCount,
                    'end' => $startCount + $eachGift['quantity'],
                ];
                $startCount = $startCount + $eachGift['quantity'];
            }

        }

        /***
         * tạo 1 số random từ 1 đến tổng tỉ lệ
         */
        $number = random_int(1, $sumRatio);

        foreach ($listGiftWheel as $eachGift) {
            if ($eachGift['start'] < $number && $eachGift['end'] >= $number) {
                $giftResult = $eachGift;
                break;
            }
        }

        if (!$giftResult) {
            $giftResult = $this->defaultGift;
        }

        return $giftResult;
    }


}
