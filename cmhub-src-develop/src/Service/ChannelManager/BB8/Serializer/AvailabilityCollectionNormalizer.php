<?php

namespace App\Service\ChannelManager\BB8\Serializer;

use App\Entity\Availability;
use App\Exception\CmHubException;
use App\Exception\DateFormatException;
use App\Exception\PartnerNotFoundException;
use App\Exception\ProductNotFoundException;
use App\Exception\ValidationException;
use App\Model\Factory\AvailabilityFactory;
use App\Model\Factory\ProductAvailabilityCollectionFactory;
use App\Model\ProductAvailability;
use App\Model\ProductAvailabilityCollection;
use App\Model\RatePlanCode;
use App\Service\Loader\PartnerLoader;
use App\Service\Loader\ProductLoader;
use App\Service\Serializer\NormalizerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;

/**
 * Class AvailabilityNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class AvailabilityCollectionNormalizer implements NormalizerInterface
{

    public const BB8_TYPE_UNAVAILABLE = 'unavailable';
    public const BB8_TYPE_INSTANT = 'instant';
    private const TYPES = [self::BB8_TYPE_INSTANT, self::BB8_TYPE_UNAVAILABLE];

    /** @var PartnerLoader $partnerLoader */
    protected $partnerLoader;

    /** @var ProductLoader $productLoader */
    protected $productLoader;

    /** @var EntityManagerInterface $entityManager */
    protected $entityManager;

    /** @var ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory */
    protected $productAvailabilityCollectionFactory;

    /** @var AvailabilityFactory $availabilityFactory */
    protected $availabilityFactory;

    /**
     * AvailabilityCollectionNormalizer constructor.
     *
     * @param PartnerLoader $partnerLoader
     * @param ProductLoader $productLoader
     * @param EntityManagerInterface $entityManager
     * @param ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory
     * @param AvailabilityFactory $availabilityFactory
     */
    public function __construct(PartnerLoader $partnerLoader, ProductLoader $productLoader, EntityManagerInterface $entityManager, ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory, AvailabilityFactory $availabilityFactory)
    {
        $this->partnerLoader = $partnerLoader;
        $this->productLoader = $productLoader;
        $this->entityManager = $entityManager;
        $this->productAvailabilityCollectionFactory = $productAvailabilityCollectionFactory;
        $this->availabilityFactory = $availabilityFactory;
    }

    /**
     *
     * @param ProductAvailability[] $availabilities
     * @param array $context
     *
     * @return mixed
     */
    public function normalize($availabilities, array $context = array())
    {
        $availabilityCollection = [];

        /** @var ProductAvailability $productAvailability */
        foreach ($availabilities as $productAvailability) {
            foreach ($productAvailability->getAvailabilities() as $availability) {
                $availabilityData = [
                    'date' => $availability->getStart()->format('Y-m-d'),
                    'quantity' => $availability->getStock(),
                    'externalRateBandId' => RatePlanCode::SBX,
                    'externalPartnerId' => $availability->getProduct()->getPartner()->getIdentifier(),
                    'externalRoomId' => $availability->getProduct()->getIdentifier(),
                    'type' => $availability->isStopSale() ? self::BB8_TYPE_UNAVAILABLE : self::BB8_TYPE_INSTANT,
                ];

                if ($availability instanceof Availability) {
                    $availabilityData['externalCreatedAt'] = $availability->getCreatedAt()->format('Y-m-d\TH:i:sP');
                    $availabilityData['externalUpdatedAt'] = $availability->getUpdatedAt()->format('Y-m-d\TH:i:sP');
                }

                $availabilityCollection[] = $availabilityData;
            }
        }

        return $availabilityCollection;
    }

    /**
     *
     * @param mixed $data
     * @param array $context
     *
     * @return ProductAvailabilityCollection
     *
     * @throws PartnerNotFoundException
     * @throws ProductNotFoundException
     * @throws \Exception
     */
    public function denormalize($data, array $context = array())
    {
        if (empty($data)) {
            throw new CmHubException('Request is empty');
        }

        if (!isset($data[0]->externalPartnerId)) {
            throw new CmHubException('Missing `externalPartnerId`');
        }

        $partner = $this->partnerLoader->find($data[0]->externalPartnerId);

        if (!$partner) {
            throw new PartnerNotFoundException($data[0]->externalPartnerId);
        }

        $collection = $this->productAvailabilityCollectionFactory->create($partner);

        $products = $partner->getProducts();
        foreach ($data as $availabilityData) {
            $filteredProducts = array_filter($products, function ($element) use ($availabilityData) {
                return $element->getIdentifier() === $availabilityData->externalRoomId;
            });

            if (empty($filteredProducts)) {
                throw new ProductNotFoundException($partner, $availabilityData->externalRoomId);
            }

            $product = reset($filteredProducts);

            $date = \DateTime::createFromFormat('Y-m-d', $availabilityData->date);
            $startDate = $date;
            $endDate = $date;

            if (!$date) {
                throw new DateFormatException('Y-m-d');
            }

            $quantity = $availabilityData->quantity;
            if ($quantity < 0) {
                throw new ValidationException(sprintf('The stock must be greater or equals to 0. Negative value `%s` has been provided.', $quantity));
            }

            if ($quantity > 99999) {
                throw new ValidationException('The maximum stock value allowed is 99999');
            }

            if (!in_array($availabilityData->type, self::TYPES)) {
                throw new ValidationException(sprintf('Availability types are `%s` or `%s`. The type `%s` is not valid.', self::BB8_TYPE_UNAVAILABLE, self::BB8_TYPE_INSTANT, $availabilityData->type));
            }

            $collection
                ->addAvailability(
                    $this
                        ->availabilityFactory
                        ->create(
                            $startDate,
                            $endDate,
                            $quantity,
                            $product,
                            self::BB8_TYPE_UNAVAILABLE === $availabilityData->type ? true : false,
                        )
                );
        }

        return $collection;
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsNormalization(string $class): bool
    {
        return ProductAvailabilityCollection::class === $class;
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
