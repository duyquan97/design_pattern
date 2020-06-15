<?php

namespace App\Service\ChannelManager\BB8\Serializer;

use App\Entity\Product;
use App\Model\ProductCollection;
use App\Service\Serializer\NormalizerInterface;
use Symfony\Component\Intl\Exception\NotImplementedException;

/**
 * Class AvailabilityNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductCollectionNormalizer implements NormalizerInterface
{
    /**
     *
     * @param ProductCollection $object
     * @param array $context
     *
     * @return mixed
     */
    public function normalize($object, array $context = array())
    {
        $data = [];

        /** @var Product $product */
        foreach ($object->getProducts() as $product) {
            $data[] = [
                'title'             => $product->getName(),
                'isSellable'          => $product->isSellable() ,
                'isReservable'        => $product->isReservable() ,
                'externalPartnerId' => $product->getPartner()->getIdentifier(),
                'externalId'        => $product->getIdentifier(),
                'description'       => $product->getDescription(),
                'isMaster'          => $product->isMaster() ,
                'externalCreatedAt' => $product->getCreatedAt()->format('Y-m-d\TH:i:sP'),
                'externalUpdatedAt' => $product->getUpdatedAt()->format('Y-m-d\TH:i:sP'),
            ];
        }

        return $data;
    }

    /**
     *
     * @param mixed $data
     * @param array $context
     *
     * @return mixed
     */
    public function denormalize($data, array $context = array())
    {
        throw new NotImplementedException('Method ProductCollectionNormalizer::denormalize is not implemented yet!');
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
