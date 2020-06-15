<?php
namespace App\Service\Singleton;

class Signleton1
{
    private static $instance = null;

    private function __construct()
    {
    }
    public static function getInstance(){
        if (static::$instance === null) {
            static::$instance = new Signleton1();
        }
        return static::$instance;
    }

    public function getSum(int $a, int $b){
        return $a + $b;
    }

}