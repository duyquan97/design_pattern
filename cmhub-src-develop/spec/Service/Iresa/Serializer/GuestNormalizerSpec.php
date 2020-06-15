<?php

namespace spec\App\Service\Iresa\Serializer;

use App\Model\Factory\GuestFactory;
use App\Model\Guest;
use App\Service\Iresa\Serializer\GuestNormalizer;
use PhpSpec\ObjectBehavior;

class GuestNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(GuestNormalizer::class);
    }

    function let(GuestFactory $guestFactory)
    {
        $this->beConstructedWith($guestFactory);
    }

    function it_denormalizes_guest(GuestFactory $guestFactory, Guest $guest)
    {
        $guestFactory->create()->willReturn($guest);
        $guest->setIsMain(true)->shouldBeCalled()->willReturn($guest);
        $guest->setName('Pepito')->shouldBeCalled()->willReturn($guest);
        $guest->setSurname('Los Palotes')->shouldBeCalled()->willReturn($guest);
        $guest->setAge(25)->shouldBeCalled()->willReturn($guest);
        $guest->setEmail('pepito@pepito.com')->shouldBeCalled()->willReturn($guest);
        $guest->setPhone('4564556456')->shouldBeCalled()->willReturn($guest);
        $guest->setCountry('Italy')->shouldBeCalled()->willReturn($guest);
        $guest->setCountryCode('it')->shouldBeCalled()->willReturn($guest);
        $guest->setAddress('Spaghetti Street')->shouldBeCalled()->willReturn($guest);
        $guest->setCity('Bambino City')->shouldBeCalled()->willReturn($guest);
        $guest->setPostalCode('545454')->shouldBeCalled()->willReturn($guest);
        $guest->setState('Ravioli')->shouldBeCalled()->willReturn($guest);

        $this
            ->denormalize(
                (object) [
                    'isMain'      => true,
                    'name'        => 'Pepito',
                    'surname'     => 'Los Palotes',
                    'age'         => 25,
                    'email'       => 'pepito@pepito.com',
                    'phone'       => '4564556456',
                    'country'     => 'Italy',
                    'countryCode' => 'it',
                    'address'     => 'Spaghetti Street',
                    'city'        => 'Bambino City',
                    'zip'         => '545454',
                    'state'       => 'Ravioli'
                ]
            )
            ->shouldBe($guest);
    }

    function it_only_denormalizes_guest()
    {
        $this->supportsDenormalization(Guest::class)->shouldBe(true);
        $this->supportsDenormalization(\stdClass::class)->shouldBe(false);
    }

    function it_does_not_support_normalization()
    {
        $this->supportsNormalization(Guest::class)->shouldBe(false);
        $this->supportsNormalization(\stdClass::class)->shouldBe(false);
    }
}
