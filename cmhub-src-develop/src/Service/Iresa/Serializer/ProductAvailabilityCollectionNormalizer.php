<?php

namespace App\Service\Iresa\Serializer;

use App\Model\Factory\ProductAvailabilityCollectionFactory;
use App\Model\ProductAvailabilityCollection;
use App\Model\ProductAvailabilityCollectionInterface;
use App\Service\Serializer\NormalizerInterface;

/**
 * Class ProductAvailabilityNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductAvailabilityCollectionNormalizer implements NormalizerInterface
{
    /**
     *
     * @var ProductAvailabilityNormalizer
     */
    private $productAvailabilityNormalizer;

    /**
     *
     * @var ProductAvailabilityCollectionFactory
     */
    private $productAvailabilityCollectionFactory;

    /**
     * ProductAvailabilityCollectionNormalizer constructor.
     *
     * @param ProductAvailabilityNormalizer        $productAvailabilityNormalizer
     * @param ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory
     */
    public function __construct(ProductAvailabilityNormalizer $productAvailabilityNormalizer, ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory)
    {
        $this->productAvailabilityNormalizer = $productAvailabilityNormalizer;
        $this->productAvailabilityCollectionFactory = $productAvailabilityCollectionFactory;
    }

    /**
     *
     * @param \stdClass $data
     * @param array     $context
     *
     * @return ProductAvailabilityCollection
     */
    public function denormalize($data, array $context = array())
    {
        $collection = $this->productAvailabilityCollectionFactory->create($context['partner']);
        foreach ($data as $availability) {
            $productAvailability = $this->productAvailabilityNormalizer->denormalize($availability, $context);
            if (!$productAvailability) {
                continue;
            }

            $collection->addProductAvailability($productAvailability);
        }

        return $collection;
    }

    /**
     *
     * @param ProductAvailabilityCollectionInterface $object
     * @param array                                  $context
     *
     * @return array
     */
    public function normalize($object, array $context = array())
    {
        $data = [
            'partnerCode'    => $object->getPartner()->getIdentifier(),
            'availabilities' => [],
        ];

        foreach ($object->getProductAvailabilities() as $productAvailability) {
            $data['availabilities'][] = [
                'roomTypeCode' => $productAvailability->getProduct()->getIdentifier(),
                'roomTypes'    => $this->productAvailabilityNormalizer->normalize($productAvailability),
            ];
        }

        return $data;
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsNormalization(string $class): bool
    {
        return $this->supports($class);
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsDenormalization(string $class): bool
    {
        return $this->supports($class);
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    private function supports(string $class): bool
    {
        return ProductAvailabilityCollection::class === $class;
    }
}
