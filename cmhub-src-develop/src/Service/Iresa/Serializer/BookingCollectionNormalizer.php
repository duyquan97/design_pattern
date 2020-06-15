<?php

namespace App\Service\Iresa\Serializer;

use App\Exception\ProductNotFoundException;
use App\Model\BookingCollection;
use App\Model\Factory\BookingCollectionFactory;
use App\Model\PartnerInterface;
use App\Service\Serializer\NormalizerInterface;

/**
 * Class BookingCollectionNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingCollectionNormalizer implements NormalizerInterface
{
    /**
     *
     * @var BookingCollectionFactory
     */
    private $bookingCollectionFactory;

    /**
     *
     * @var BookingNormalizer
     */
    private $bookingNormalizer;

    /**
     * BookingCollectionNormalizer constructor.
     *
     * @param BookingCollectionFactory $bookingCollectionFactory
     * @param BookingNormalizer        $bookingNormalizer
     */
    public function __construct(BookingCollectionFactory $bookingCollectionFactory, BookingNormalizer $bookingNormalizer)
    {
        $this->bookingCollectionFactory = $bookingCollectionFactory;
        $this->bookingNormalizer = $bookingNormalizer;
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
        // TODO: Implement normalize() method.
    }

    /**
     *
     * @param mixed $data
     * @param array $context
     *
     * @return BookingCollection|mixed
     *
     * @throws ProductNotFoundException
     */
    public function denormalize($data, array $context = array())
    {
        $bookingCollection = $this->bookingCollectionFactory->create();

        foreach ($data as $booking) {
            $bookingModel = $this->bookingNormalizer->denormalize($booking, $context);
            $bookingCollection->addBooking($bookingModel);
        }

        return $bookingCollection;
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
        return BookingCollection::class === $class;
    }
}
