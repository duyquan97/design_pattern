<?php

namespace App\Service\ChannelManager\Wubook\Serializer;

use App\Entity\Guest;
use App\Model\GuestInterface;
use App\Service\Serializer\NormalizerInterface;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

/**
 * Class GuestNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class GuestNormalizer implements NormalizerInterface
{
    /**
     *
     * @param GuestInterface $guest
     * @param array          $context
     *
     * @return array
     */
    public function normalize($guest, array $context = array())
    {
        return [
            'first_name' => $guest->getName() ?: '',
            'last_name'  => $guest->getSurname() ?: '',
            'email'      => $guest->getEmail() ?: '',
            'phone'      => $guest->getPhone() ?: '',
            'country'    => $guest->getCountryCode() ?: '',
            'city'       => $guest->getCity() ?: '',
            'address'    => $guest->getAddress() ?: '',
            'zip'        => $guest->getPostalCode() ?: '',
        ];
    }

    /**
     *
     * @param mixed $data
     * @param array $context
     *
     * @return mixed|void
     */
    public function denormalize($data, array $context = array())
    {
        throw new MethodNotImplementedException('Method Guest::denormalize is not implemented');
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
