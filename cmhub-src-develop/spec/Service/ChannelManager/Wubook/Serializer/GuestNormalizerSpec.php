<?php

namespace spec\App\Service\ChannelManager\Wubook\Serializer;

use App\Entity\Booking;
use App\Entity\Guest;
use App\Model\GuestInterface;
use App\Service\ChannelManager\Wubook\Serializer\GuestNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

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

    function it_doesnt_denormalize()
    {
        $request = [
            "start_time" => "2014-04-25 15:00:00"
        ];

        $this->shouldThrow(MethodNotImplementedException::class)->during('denormalize', [json_encode($request), []]);
    }

    function it_normalizes_valid_data(GuestInterface $guest)
    {
        $customer =[
            "first_name" => "John",
            "last_name" => "Doe",
            "email" => "johndoe@gmail.com",
            "phone" => "12345678",
            "country" => "IT",
            "city" => "Milan",
            "address" => "Viale Vittoria, 3",
            "zip" => "20133",
        ];

        $guest->getName()->willReturn("John");
        $guest->getSurname()->willReturn("Doe");
        $guest->getEmail()->willReturn("johndoe@gmail.com");
        $guest->getPhone()->willReturn("12345678");
        $guest->getCountryCode()->willReturn("IT");
        $guest->getCity()->willReturn("Milan");
        $guest->getAddress()->willReturn("Viale Vittoria, 3");
        $guest->getPostalCode()->willReturn("20133");

        $this->normalize($guest, [])->shouldBeLike($customer);
    }

    function it_normalizes_some_null_data(GuestInterface $guest)
    {
        $customer =[
            "first_name" => "John",
            "last_name" => "Doe",
            "email" => "johndoe@gmail.com",
            "phone" => "12345678",
            "country" => "IT",
            "city" => "",
            "address" => "",
            "zip" => "",
        ];

        $guest->getName()->willReturn("John");
        $guest->getSurname()->willReturn("Doe");
        $guest->getEmail()->willReturn("johndoe@gmail.com");
        $guest->getPhone()->willReturn("12345678");
        $guest->getCountryCode()->willReturn("IT");
        $guest->getCity()->willReturn(null);
        $guest->getAddress()->willReturn(null);
        $guest->getPostalCode()->willReturn(null);

        $this->normalize($guest, [])->shouldBeLike($customer);
    }

    function it_supports_normalization_for()
    {
        $this->supportsNormalization(Guest::class)->shouldBe(true);
    }

    function it_doesnt_support_denormalization_for()
    {
        $this->supportsDenormalization(Guest::class)->shouldBe(false);
    }

    function it_doesnt_support_normalization_for()
    {
        $this->supportsNormalization(Booking::class)->shouldBe(false);
    }
}
