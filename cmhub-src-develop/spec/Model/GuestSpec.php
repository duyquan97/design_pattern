<?php

namespace spec\App\Model;

use App\Model\Guest;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GuestSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Guest::class);
    }

    function it_sets_and_gets_main_attribute()
    {
        $this->setIsMain(true);
        $this->isMain()->shouldBe(true);

        $this->setIsMain(false);
        $this->isMain()->shouldBe(false);
    }

    function it_sets_and_gets_age_attribute()
    {
        $this->setAge(18);
        $this->getAge()->shouldBe(18);

        $this->setAge(null);
        $this->getAge()->shouldBe(null);
    }

    function it_sets_and_gets_name_attribute()
    {
        $this->setName('Agapito');
        $this->getName()->shouldBe('Agapito');

        $this->setName(null);
        $this->getName()->shouldBe(null);
    }

    function it_sets_and_gets_surname_attribute()
    {
        $this->setSurname('Hernandez');
        $this->getSurname()->shouldBe('Hernandez');

        $this->setSurname(null);
        $this->getSurname()->shouldBe(null);
    }

    function it_sets_and_gets_email_attribute()
    {
        $this->setEmail('unknown@smartbox.com');
        $this->getEmail()->shouldBe('unknown@smartbox.com');

        $this->setEmail(null);
        $this->getEmail()->shouldBe(null);
    }

    function it_sets_and_gets_phone_attribute()
    {
        $this->setPhone('899756820');
        $this->getPhone()->shouldBe('899756820');

        $this->setPhone(null);
        $this->getPhone()->shouldBe(null);
    }

    function it_sets_and_gets_country_attribute()
    {
        $this->setCountry('fr');
        $this->getCountry()->shouldBe('fr');

        $this->setCountry(null);
        $this->getCountry()->shouldBe(null);
    }

    function it_sets_and_gets_countrycode_attribute()
    {
        $this->setCountryCode('00124586');
        $this->getCountryCode()->shouldBe('00124586');

        $this->setCountryCode(null);
        $this->getCountryCode()->shouldBe(null);
    }

    function it_sets_and_gets_address_attribute()
    {
        $this->setAddress('c/ piruleta');
        $this->getAddress()->shouldBe('c/ piruleta');

        $this->setAddress(null);
        $this->getAddress()->shouldBe(null);
    }

    function it_sets_and_gets_city_attribute()
    {
        $this->setCity('Cadiz capital del mundo');
        $this->getCity()->shouldBe('Cadiz capital del mundo');

        $this->setCity(null);
        $this->getCity()->shouldBe(null);
    }

    function it_sets_and_gets_postalcode_attribute()
    {
        $this->setPostalCode('11405');
        $this->getPostalCode()->shouldBe('11405');

        $this->setPostalCode(null);
        $this->getPostalCode()->shouldBe(null);
    }

    function it_sets_and_gets_state_attribute()
    {
        $this->setState('Cadiz');
        $this->getState()->shouldBe('Cadiz');

        $this->setState(null);
        $this->getState()->shouldBe(null);
    }
}
