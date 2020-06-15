<?php

namespace App\Service\ChannelManager\Wubook\Serializer;

use App\Exception\DateFormatException;
use App\Exception\ProductNotFoundException;
use App\Exception\ValidationException;
use App\Model\Availability;
use App\Model\Factory\AvailabilityFactory;
use App\Model\WubookErrorCode;
use App\Service\Loader\ProductLoader;
use App\Service\Serializer\NormalizerInterface;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

/**
 * Class RestrictionNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class RestrictionNormalizer implements NormalizerInterface
{
    /**
     * @var AvailabilityFactory
     */
    private $availabilityFactory;

    /**
     * @var ProductLoader
     */
    private $productLoader;

    /**
     * AvailabilityNormalizer constructor.
     *
     * @param AvailabilityFactory $availabilityFactory
     * @param ProductLoader       $productLoader
     */
    public function __construct(AvailabilityFactory $availabilityFactory, ProductLoader $productLoader)
    {
        $this->availabilityFactory = $availabilityFactory;
        $this->productLoader = $productLoader;
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
        throw new MethodNotImplementedException('Method Availability::normalize is not implemented');
    }

    /**
     *
     * @param mixed $restriction
     * @param array $context
     *
     * @return Availability|mixed
     *
     * @throws DateFormatException
     * @throws ProductNotFoundException
     * @throws ValidationException
     */
    public function denormalize($restriction, array $context = array())
    {
        if (!isset($restriction->dfrom) || !isset($restriction->dto)) {
            throw new ValidationException('Dates must be defined');
        }

        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $restriction->dfrom)) {
            throw new DateFormatException('Y-m-d');
        }

        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $restriction->dto)) {
            throw new DateFormatException('Y-m-d');
        }

        $startDate = \DateTime::createFromFormat('Y-m-d', $restriction->dfrom);
        $endDate = \DateTime::createFromFormat('Y-m-d', $restriction->dto);

        if ($startDate > $endDate) {
            throw new ValidationException("Start date cannot be greater than end date");
        }

        $partner = $context['partner'];

        if (!isset($restriction->room_id)) {
            throw new ValidationException("Room id is not defined");
        }

        $product = $this->productLoader->find($partner, $roomId = $restriction->room_id);

        if (!$product) {
            throw new ProductNotFoundException($partner, $roomId, WubookErrorCode::PRODUCT_NOT_FOUND);
        }

        $availabilityRestriction = null;
        $cursor = clone $startDate;
        $stopSale = (bool) $restriction->closed;
        $productAvailabilityCollection = $context['collection'];
        while ($cursor <= $endDate) {
            $availabilityRestriction = $productAvailabilityCollection->getByProductAndDate($product, $cursor);
            if (!$availabilityRestriction) {
                $availabilityRestriction = $this->availabilityFactory->create(
                    clone $cursor,
                    clone $cursor,
                    null,
                    $product
                );
            }
            $availabilityRestriction->setStopSale($stopSale);
            $productAvailabilityCollection->addAvailability($availabilityRestriction);
            $cursor->modify('+1 day');
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
        return Availability::class === $class;
    }
}
