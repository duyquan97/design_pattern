<?php

namespace App\Service\Iresa\Serializer;

use App\Model\Factory\GuestFactory;
use App\Model\Guest;
use App\Service\Serializer\NormalizerInterface;

/**
 * Class GuestNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class GuestNormalizer implements NormalizerInterface
{
    /**
     *
     * @var GuestFactory
     */
    private $guestFactory;

    /**
     * GuestNormalizer constructor.
     *
     * @param GuestFactory $guestFactory
     */
    public function __construct(GuestFactory $guestFactory)
    {
        $this->guestFactory = $guestFactory;
    }

    /**
     *
     * @param mixed $object
     * @param array $context
     *
     * @return mixed
     */
    public function normalize($object, array $context = array())
    {
        // TODO: Implement normalize() method.
    }

    /**
     *
     * @param mixed $guest
     * @param array $context
     *
     * @return Guest
     */
    public function denormalize($guest, array $context = array())
    {
        return $this
            ->guestFactory
            ->create()
            ->setIsMain(isset($guest->isMain) ? $guest->isMain : false)
            ->setName(isset($guest->name) ? $guest->name : '')
            ->setSurname(isset($guest->surname) ? $guest->surname : '')
            ->setAge(isset($guest->age) ? $guest->age : 0)
            ->setEmail(isset($guest->email) ? $guest->email : '')
            ->setPhone(isset($guest->phone) ? $guest->phone : '')
            ->setCountry(isset($guest->country) ? $guest->country : '')
            ->setCountryCode(isset($guest->countryCode) ? $guest->countryCode : '')
            ->setAddress(isset($guest->address) ? $guest->address : '')
            ->setCity(isset($guest->city) ? $guest->city : '')
            ->setPostalCode(isset($guest->zip) ? $guest->zip : '')
            ->setState(isset($guest->state) ? $guest->state : '');
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsNormalization(string $class): bool
    {
        return false;
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsDenormalization(string $class): bool
    {
        return Guest::class === $class;
    }
}
