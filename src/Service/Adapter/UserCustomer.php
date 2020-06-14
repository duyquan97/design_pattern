<?php
namespace App\Service\Adapter;

class UserCustomer implements CustomerIF
{
    protected $user;
    protected $firstName;
    protected $lastName;
    public function __construct(User $user)
    {
        $this->user = $user;

        $fullName = $this->user->getName();

        $pieces = explode(' ', $fullName);

        $this->firstName = $pieces[0];
        $this->lastName = $pieces[1];
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $firstName;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $lastName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }


}
