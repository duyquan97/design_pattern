<?php

namespace App\Service\Iresa\Serializer;

use App\Repository\AvailabilityRepository;
use App\Model\Availability;
use App\Model\Factory\ProductAvailabilityFactory;
use App\Model\ProductAvailability;
use App\Model\ProductAvailabilityInterface;
use App\Service\Loader\ProductLoader;
use App\Service\Serializer\NormalizerInterface;

/**
 * Class ProductAvailabilityNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductAvailabilityNormalizer implements NormalizerInterface
{
    /**
     *
     * @var ProductLoader
     */
    private $productLoader;

    /**
     *
     * @var ProductAvailabilityFactory
     */
    private $productAvailabilityFactory;

    /**
     * @var AvailabilityRepository
     */
    private $availabilityRepository;

    /**
     *
     * @var AvailabilityNormalizer
     */
    private $availabilityNormalizer;

    /**
     * ProductAvailabilityNormalizer constructor.
     *
     * @param ProductLoader              $productLoader
     * @param ProductAvailabilityFactory $productAvailabilityFactory
     * @param AvailabilityRepository     $availabilityRepository
     * @param AvailabilityNormalizer     $availabilityNormalizer
     */
    public function __construct(ProductLoader $productLoader, ProductAvailabilityFactory $productAvailabilityFactory, AvailabilityRepository $availabilityRepository, AvailabilityNormalizer $availabilityNormalizer)
    {
        $this->productLoader = $productLoader;
        $this->productAvailabilityFactory = $productAvailabilityFactory;
        $this->availabilityRepository = $availabilityRepository;
        $this->availabilityNormalizer = $availabilityNormalizer;
    }

    /**
     *
     * @param ProductAvailabilityInterface $object
     * @param array                        $context
     *
     * @return mixed
     */
    public function normalize($object, array $context = array())
    {
        $data = [];

        /** @var Availability $availability */
        foreach ($object->getAvailabilities() as $availability) {
            $stock = 0;

            if (!$availability->isStopSale() && null === $availability->getStock()) {
                $entity = $this->availabilityRepository->findOneBy(
                    [
                        'date'    => $availability->getStart(),
                        'product' => $availability->getProduct(),
                    ]
                );

                if ($entity) {
                    $stock = $entity->getStock();
                }
            }

            if (!$availability->isStopSale() && null !== $availability->getStock()) {
                $stock = $availability->getStock();
            }

            $data[] = [
                'dateStart' => $availability->getStart()->format('Y-m-d'),
                'dateEnd'   => (clone $availability->getEnd())->modify('+1 day')->format('Y-m-d'),
                'stock'     => $stock,
            ];
        }

        return $data;
    }

    /**
     *
     * @param mixed $availability
     * @param array $context
     *
     * @return ProductAvailability|null
     */
    public function denormalize($availability, array $context = array())
    {
        $product = $this->productLoader->find($partner = $context['partner'], $availability->roomTypeCode);
        if (!$product) {
            // TODO: Log room not found error
            return null;
        }

        $productAvailability = $this
            ->productAvailabilityFactory
            ->create($product)
            ->setPartner($partner);

        foreach ($availability->stocks as $stock) {
            $availabilityObject = $this->availabilityNormalizer->denormalize($stock, ['product' => $product]);

            $productAvailability
                ->addAvailability($availabilityObject);
        }

        return $productAvailability;
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsNormalization(string $class): bool
    {
        return ProductAvailability::class === $class;
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsDenormalization(string $class): bool
    {
        return ProductAvailability::class === $class;
    }
}
