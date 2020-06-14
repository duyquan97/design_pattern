<?php

namespace App\Controller;

use App\Service\Adapter\User;
use App\Service\Adapter\UserCustomer;
use App\Service\BuilderSet;
use App\Service\Factory;
use App\Service\FactoryAbstract;
use App\Service\Signleton1;
use App\Service\Signleton2;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Routing\Annotation\Route;


class SingletonController extends AbstractController
{

    /**
     * Creates an Article resource
     * @Route("/")
     */
    public function index(){
        $sign = Signleton1::getInstance();
        dd($sign->getSum(5,6));
    }

    /**
     * Creates an Article resource
     * @Route("/factory")
     */
    public function factory(){
        $factory = new Factory();
        $factory->createProduct('Quan');
        dd($factory->createProduct('Quan')->getName());
    }

    /**
     * Creates an Article resource
     * @Route("/builder")
     */
    public function builder(){
        $builder = new BuilderSet();
        $builder->setAge(23);
        $builder->setName('Quan');

        dd($builder->build());
    }

    /**
     * Creates an Article resource
     * @Route("/adapter")
     */
    public function adapter(){
        $user = new User();
        $user->setName('Duy Quan');

        $adapter = new UserCustomer($user);

        dd($adapter);

    }

}
