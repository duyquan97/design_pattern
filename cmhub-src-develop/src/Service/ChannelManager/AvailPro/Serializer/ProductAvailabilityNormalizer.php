<?php

namespace App\Service\ChannelManager\AvailPro\Serializer;

use App\Exception\CmHubException;
use App\Exception\DateFormatException;
use App\Exception\PartnerNotFoundException;
use App\Exception\ProductNotFoundException;
use App\Exception\ValidationException;
use App\Model\Factory\AvailabilityFactory;
use App\Model\Factory\ProductAvailabilityCollectionFactory;
use App\Model\ProductAvailabilityCollection;
use App\Service\ChannelManager\AvailPro\AvailProChannelManager;
use App\Service\Loader\PartnerLoader;
use App\Service\Loader\ProductLoader;
use App\Service\Serializer\NormalizerInterface;
use App\Utils\Monolog\CmhubLogger;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

/**
 * Class ProductAvailabilityNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductAvailabilityNormalizer implements NormalizerInterface
{
    /**
     *
     * @var PartnerLoader
     */
    private $partnerLoader;

    /**
     *
     * @var ProductLoader
     */
    private $productLoader;

    /**
     *
     * @var ProductAvailabilityCollectionFactory
     */
    private $productAvailabilityCollectionFactory;

    /**
     *
     * @var AvailabilityFactory
     */
    private $availabilityFactory;

    /**
     * @var CmhubLogger
     */
    private $logger;

    /**
     * ProductAvailabilityNormalizer constructor.
     *
     * @param PartnerLoader $partnerLoader
     * @param ProductLoader $productLoader
     * @param ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory
     * @param AvailabilityFactory $availabilityFactory
     * @param CmhubLogger $logger
     */
    public function __construct(PartnerLoader $partnerLoader, ProductLoader $productLoader, ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory, AvailabilityFactory $availabilityFactory, CmhubLogger $logger)
    {
        $this->partnerLoader = $partnerLoader;
        $this->productLoader = $productLoader;
        $this->productAvailabilityCollectionFactory = $productAvailabilityCollectionFactory;
        $this->availabilityFactory = $availabilityFactory;
        $this->logger = $logger;
    }

    /**
     *
     * @param mixed $object
     * @param array $context
     *
     * @return void
     */
    public function normalize($object, array $context = array())
    {
        throw new MethodNotImplementedException('Method AvailPro/ProductAvailabilityCollection::normalize is not implemented');
    }

    /**
     *
     * @param \stdClass $data
     * @param array $context
     *
     * @return ProductAvailabilityCollection
     *
     * @throws DateFormatException
     * @throws PartnerNotFoundException
     * @throws ProductNotFoundException
     * @throws ValidationException
     */
    public function denormalize($data, array $context = array()): ProductAvailabilityCollection
    {
        $partner = $this->partnerLoader->find($partnerId = $data->inventoryUpdate->{'@attributes'}->hotelId);
        if (!$partner) {
            throw new PartnerNotFoundException($partnerId);
        }
        if ($partner->getChannelManager() && ($partner->getChannelManager()->getIdentifier() !== AvailProChannelManager::NAME)) {
            throw new PartnerNotFoundException($partnerId);
        }
        $collection = $this->productAvailabilityCollectionFactory->create($partner);
        foreach ($data->inventoryUpdate->room as $room) {
            if (!$product = $this->productLoader->find($partner, $roomId = $room->{'@attributes'}->id)) {
                $this->logger->addOperationException('', new ProductNotFoundException($partner, $roomId), $this);
                continue;
            }

            if (!isset($room->inventory)) {
                continue;
            }

            if (!is_array($room->inventory->availability) && null !== $room->inventory->availability) {
                $room->inventory->availability = [$room->inventory->availability];
            }
            $availabilities = [];
            foreach ($room->inventory->availability as $availability) {
                $attributes = $availability->{'@attributes'};

                $startDate = \DateTime::createFromFormat('Y-m-d', $attributes->from);
                $endDate = \DateTime::createFromFormat('Y-m-d', $attributes->to);
                if (!$startDate || !$endDate) {
                    throw new DateFormatException('Y-m-d');
                }

                $quantity = $attributes->quantity;
                if ($quantity < 0) {
                    throw new ValidationException(sprintf('The stock must be greater or equals to 0. Negative value `%s` has been provided.', $quantity));
                }

                if ($quantity > 99999) {
                    throw new ValidationException('The maximum stock value allowed is 99999');
                }

                $key = sprintf('%s:%s', $attributes->from, $attributes->to);
                if (!array_key_exists($key, $availabilities)) {
                    $availabilities[$key] = [
                        'start' => $startDate,
                        'end' => $endDate,
                        'quantity' => $quantity,
                        'closed' => false,
                    ];
                }
            }

            if (isset($room->rate)) {
                foreach ($room->rate->planning as $planning) {
                    $attributes = $planning;
                    if (isset($planning->{'@attributes'})) {
                        $attributes = $planning->{'@attributes'};
                    }

                    if (!isset($attributes->isClosed) || !isset($attributes->from) || !isset($attributes->to)) {
                        continue;
                    }

                    $key = sprintf('%s:%s', $attributes->from, $attributes->to);
                    $availability = null;
                    if (array_key_exists($key, $availabilities)) {
                        $availability = $availabilities[$key];
                        $availability['closed'] = filter_var($attributes->isClosed, FILTER_VALIDATE_BOOLEAN);
                    }

                    if (!$availability) {
                        $availability = [
                            'start' => \DateTime::createFromFormat('Y-m-d', $attributes->from),
                            'end' => \DateTime::createFromFormat('Y-m-d', $attributes->to),
                            'quantity' => null,
                            'closed' => filter_var($attributes->isClosed, FILTER_VALIDATE_BOOLEAN),
                        ];
                    }

                    $availabilities[$key] = $availability;
                }
            }

            foreach ($availabilities as $availability) {
                $newAvailability = $this->availabilityFactory->create(
                    $availability['start'],
                    $availability['end'],
                    $availability['quantity'],
                    $product
                );
                $newAvailability->setStopSale($availability['closed']);
                $collection->addAvailability($newAvailability);
            }
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
        if (ProductAvailabilityCollection::class === $class) {
            return true;
        }

        return false;
    }
}
