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
 * Class AvailabilityNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class AvailabilityNormalizer implements NormalizerInterface
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
     * @param mixed $availability
     * @param array $context
     *
     * @return Availability|mixed
     *
     * @throws DateFormatException
     * @throws ProductNotFoundException
     * @throws ValidationException
     */
    public function denormalize($availability, array $context = array())
    {
        if (!isset($availability->dfrom) || !isset($availability->dto)) {
            throw new ValidationException('Dates must be defined');
        }

        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $availability->dfrom)) {
            throw new DateFormatException('Y-m-d');
        }

        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $availability->dto)) {
            throw new DateFormatException('Y-m-d');
        }

        $startDate = \DateTime::createFromFormat('Y-m-d', $availability->dfrom);
        $endDate = \DateTime::createFromFormat('Y-m-d', $availability->dto);

        if ($startDate > $endDate) {
            throw new ValidationException("Start date cannot be greater than end date");
        }

        if (!isset($availability->avail)) {
            throw new ValidationException("Stock can't be an empty value");
        }

        $stock = $availability->avail;

        if ($stock < 0) {
            throw new ValidationException("Availability cannot be less than 0");
        }

        $partner = $context['partner'];

        if (!isset($availability->room_id)) {
            throw new ValidationException("Room id is not defined");
        }

        $product = $this->productLoader->find($partner, $roomId = $availability->room_id);

        if (!$product) {
            throw new ProductNotFoundException($partner, $roomId, WubookErrorCode::PRODUCT_NOT_FOUND);
        }

        return $this
            ->availabilityFactory
            ->create(
                $startDate,
                $endDate,
                $stock,
                $product
            );
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
