<?php
namespace App\Service\Adapter;

class User implements UserIF
{
    private $name;

    public function setName($name)
    {
        $this->name = $name;
        return $name;
    }

    public function getName()
    {
        return $this->name;
    }

}
