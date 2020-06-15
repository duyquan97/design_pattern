<?php

namespace App\Service\ChannelManager\Wubook\Serializer;

use App\Model\BookingCollection;
use App\Service\Serializer\NormalizerInterface;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

/**
 * Class BookingCollectionNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingCollectionNormalizer implements NormalizerInterface
{
    /**
     * @var BookingNormalizer
     */
    private $bookingNormalizer;

    /**
     * BookingCollectionNormalizer constructor.
     *
     * @param BookingNormalizer $bookingNormalizer
     */
    public function __construct(BookingNormalizer $bookingNormalizer)
    {
        $this->bookingNormalizer = $bookingNormalizer;
    }

    /**
     *
     * @param BookingCollection $bookingCollection
     * @param array             $context
     *
     * @return array
     */
    public function normalize($bookingCollection, array $context = array())
    {
        $bookings = [];

        foreach ($bookingCollection->getBookings() as $booking) {
            $bookings[] = $this->bookingNormalizer->normalize($booking, $context);
        }

        return [
            'bookings' => $bookings,
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
        throw new MethodNotImplementedException('Method BookingCollection::denormalize is not implemented');
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsNormalization(string $class): bool
    {
        return BookingCollection::class === $class;
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
