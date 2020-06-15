<?php

namespace App\Service\ChannelManager\SoapOta\Serializer\V2007B;

use App\Model\Guest;
use App\Model\GuestInterface;
use App\Service\Serializer\NormalizerInterface;
use App\Utils\OTAAgeFormatter;

/**
 * Class GuestNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class GuestNormalizer implements NormalizerInterface
{
    /**
     *
     * @param GuestInterface $guest   The guest
     * @param array          $context The context
     *
     * @return array
     */
    public function normalize($guest, array $context = array())
    {
        return [
            'ResGuestRPH'       => $context['index'],
            'AgeQualifyingCode' => OTAAgeFormatter::DEFAULT_AGE_QUALIFYING_CODE,
            'PrimaryIndicator'  => $guest->isMain() ? 1 : 0,
            'Profiles'          => [
                'ProfileInfo' => [
                    [
                        'Profile' => [
                            'ProfileType' => 1,
                            'Customer'    => [
                                'PersonName' => [
                                    'GivenName' => $guest->getName(),
                                    'Surname'   => $guest->getSurname(),
                                ],
                                'Email'      => $guest->getEmail(),
                                'Telephone'  => [
                                    'PhoneTechType' => 1,
                                    'PhoneNumber'   => $guest->getPhone(),
                                ],
                                'Address'    => [
                                    'Type'        => '1',
                                    'AddressLine' => $guest->getAddress(),
                                    'CityName'    => $guest->getCity(),
                                    'PostalCode'  => $guest->getPostalCode(),
                                    'StateProv'   => $guest->getState(),
                                    'CountryName' => [
                                        'Code' => $guest->getCountryCode(),
                                        '_'    => $guest->getCountry(),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     *
     * @param mixed $data
     * @param array $context
     *
     * @return void
     */
    public function denormalize($data, array $context = array())
    {
        // TODO: Implement denormalize() method.
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsNormalization(string $class): bool
    {
        return Guest::class === $class;
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsDenormalization(string $class): bool
    {
        return false;
    }
}
