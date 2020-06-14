<?php
namespace App\Service\Adapter;

interface CustomerIF
{
    public function setFirstName($firstName);
    public function getFirstName();
    public function setLastName($lastName);
    public function getLastName();

}
