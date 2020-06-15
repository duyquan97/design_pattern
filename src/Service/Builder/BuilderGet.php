<?php
namespace App\Service\Builder;


class BuilderGet
{
    private $name;
    private $age;

    public function __construct($name, $age
    )
    {
        $this->name = $name;
        $this->age = $age;
    }

    public function getName(){
        return $this->name;
    }

    public function getAge(){
        return $this->age;
    }

    public function show()
    {
        $str = '';
        $str = $this->getName().' '.$this->getAge();
    }



}