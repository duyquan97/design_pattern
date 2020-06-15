<?php
namespace App\Service\Builder;


class BuilderSet implements BuilderInterface
{
    private $name;
    private $age;

    public function setName($name)
    {
        $this->name = $name;
        return $name;
    }

    public function setAge($age)
    {
        $this->age = $age;
        return $age;
    }

    public function build()
    {
        return new BuilderGet($this->name, $this->age);
    }

}