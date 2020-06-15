<?php
namespace App\Service\Builder;


interface  BuilderInterface
{
    public function setName($name);
    public function setAge($age);
    public function build();

}