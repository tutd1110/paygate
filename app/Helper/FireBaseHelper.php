<?php
namespace App\Helper;
use Kreait\Firebase\Factory;
use App\Providers\FirebaseService;

class FireBaseHelper
{
    public function setDataFireBase($param = [],$url = ''){
        $fireBaseService = new FirebaseService();
        $fireBaseService->firebase
            ->getReference($url)
            ->set($param);

    }
}
