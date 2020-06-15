<?php

namespace App\Service\ChannelManager\Wubook\Serializer;

use App\Exception\CmHubException;
use App\Exception\DateFormatException;
use App\Exception\ProductNotFoundException;
use App\Exception\ValidationException;
use App\Model\Factory\AvailabilityFactory;
use App\Model\Factory\ProductAvailabilityCollectionFactory;
use App\Model\ProductAvailabilityCollection;
use App\Service\Loader\ProductLoader;
use App\Service\Serializer\NormalizerInterface;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

/**
 * Class ProductAvailabilityCollectionNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductAvailabilityCollectionNormalizer implements NormalizerInterface
{
    /**
     * @var ProductAvailabilityCollectionFactory
     */
    private $productAvailabilityCollectionFactory;

    /**
     * @var AvailabilityNormalizer
     */
    private $availabilityNormalizer;

    /**
     * @var RestrictionNormalizer
     */
    private $restrictionNormalizer;

    /**
     * ProductAvailabilityCollectionNormalizer constructor.
     *
     * @param ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory
     * @param AvailabilityNormalizer               $availabilityNormalizer
     * @param RestrictionNormalizer                $restrictionNormalizer
     */
    public function __construct(ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory, AvailabilityNormalizer $availabilityNormalizer, RestrictionNormalizer $restrictionNormalizer)
    {
        $this->productAvailabilityCollectionFactory = $productAvailabilityCollectionFactory;
        $this->availabilityNormalizer = $availabilityNormalizer;
        $this->restrictionNormalizer = $restrictionNormalizer;
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
        throw new MethodNotImplementedException('Method ProductAvailabilityCollection::normalize is not implemented');
    }

    /**
     *
     * @param mixed $data
     * @param array $context
     *
     * @return ProductAvailabilityCollection
     *
     * @throws ValidationException
     * @throws DateFormatException
     * @throws ProductNotFoundException
     * @throws CmHubException
     */
    public function denormalize($data, array $context = array())
    {
        $productAvailabilityCollection = $this->productAvailabilityCollectionFactory->create($context['partner']);

        if (!empty($data->availability)) {
            foreach ($data->availability as $availability) {
                $availabilityObject = $this->availabilityNormalizer->denormalize($availability, $context);
                $productAvailabilityCollection->addAvailability($availabilityObject);
            }
        }

        if (!empty($data->restrictions)) {
            foreach ($data->restrictions as $restriction) {
                $context['collection'] = $productAvailabilityCollection;
                $productAvailabilityCollection = $this->restrictionNormalizer->denormalize($restriction, $context);
            }
        }

        return $productAvailabilityCollection;
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
        return ProductAvailabilityCollection::class === $class;
    }
}
