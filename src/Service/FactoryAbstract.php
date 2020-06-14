<?php
namespace App\Service;


abstract class FactoryAbstract
{
//    public function __construct($name)
//    {
//        if ($name === 'Quan') {
//            return new FactoryMethodClass();
//        }
//        if ($name === 'Thu') {
//            return new FactoryMethodClass2();
//        }
//    }

    public function createProduct(string $name) {
        if ($name === 'Quan') {
            return new FactoryMethodClass();
        }
        if ($name === 'Thu') {
            return new FactoryMethodClass2();
        }
    }

}