<?php

namespace App\Service\ChannelManager\SoapOta\Serializer\V2016A;

use App\Model\ProductCollection;
use App\Service\Serializer\NormalizerInterface;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

/**
 * Class ProductCollectionNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductCollectionNormalizer implements NormalizerInterface
{
    /**
     *
     * @param ProductCollection $productCollection
     * @param array             $context
     *
     * @return array
     */
    public function normalize($productCollection, array $context = array())
    {
        $guestRooms = [];
        foreach ($productCollection->toArray() as $product) {
            $guestRooms[] = [
                "Code"     => $product->getIdentifier(),
                "TypeRoom" => [
                    "Name" => $product->getName(),
                ],
            ];
        }

        return [
            "GuestRooms" => [
                'GuestRoom' => $guestRooms,
            ],
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
        throw new MethodNotImplementedException('Method ProductCollection::denormalize is not implemented');
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsNormalization(string $class): bool
    {
        return ProductCollection::class === $class;
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
