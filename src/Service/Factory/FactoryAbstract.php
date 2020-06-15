<?php
namespace App\Service\Factory;


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

    public function createProduct(string $name)
    {
        switch ($name) {
            case 'Quan':
                return new FactoryMethodClass();
                break;
            case 'Thu':
                return new FactoryMethodClass2();
                break;
            default:
                return null;
                break;
        }
        return null;
    }
}