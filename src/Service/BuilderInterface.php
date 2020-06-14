<?php
namespace App\Service;


interface  BuilderInterface
{
    public function setName($name);
    public function setAge($age);
    public function build();

}