<?php

namespace spec\App\Service\ChannelManager\SoapOta\Serializer\V2007B;

use App\Service\ChannelManager\SoapOta\Serializer\V2007B\GuestNormalizer;
use App\Model\Guest;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GuestNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(GuestNormalizer::class);
    }

    function let()
    {
        $this->beConstructedWith();
    }

    function it_normalizes_a_valid_object(Guest $guest)
    {
        $guest->getAge()->willReturn(30);
        $guest->getName()->willReturn('Joe');
        $guest->getSurname()->willReturn('Doe');
        $guest->getEmail()->willReturn('joe@doe.com');
        $guest->getPhone()->willReturn('12345678');
        $guest->isMain()->willReturn(true);
        $guest->getAddress()->willReturn('Joe Doe street, 123');
        $guest->getCity()->willReturn('Manhattan');
        $guest->getPostalCode()->willReturn('12345');
        $guest->getState()->willReturn('NYC');
        $guest->getCountryCode()->willReturn('US');
        $guest->getCountry()->willReturn('United States of America');

        $this->normalize($guest, ['index' => 1])->shouldBe([
            'ResGuestRPH'       => 1,
            'AgeQualifyingCode' => 10,
            'PrimaryIndicator'  => 1,
            'Profiles'          => [
                'ProfileInfo' => [
                    [
                        'Profile' => [
                            'ProfileType' => 1,
                            'Customer' => [
                                'PersonName' => [
                                    'GivenName' => 'Joe',
                                    'Surname'   => 'Doe',
                                ],
                                'Email' => 'joe@doe.com',
                                'Telephone' => [
                                    'PhoneTechType' => 1,
                                    'PhoneNumber'   => '12345678',
                                ],
                                'Address' => [
                                    'Type' => '1',
                                    'AddressLine' => 'Joe Doe street, 123',
                                    'CityName'    => 'Manhattan',
                                    'PostalCode'  => '12345',
                                    'StateProv'   => 'NYC',
                                    'CountryName' => [
                                        'Code' => 'US',
                                        '_'    => 'United States of America',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    function it_denormalizes_a_valid_object(Guest $guest)
    {
        $this->denormalize([])->shouldBe(null);
    }

    function it_supports_normalization_for()
    {
        $this->supportsNormalization('App\Model\Guest')->shouldBe(true);
    }

    function it_supports_denormalization_for()
    {
        $this->supportsDenormalization('App\Model\Guest')->shouldBe(false);
    }
}
