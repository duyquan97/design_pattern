<?php

namespace App\Service\ChannelManager\Wubook\Serializer;

use App\Entity\BookingProduct;
use App\Model\BookingProductInterface;
use App\Model\GuestInterface;
use App\Model\RateInterface;
use App\Model\RatePlanCode;
use App\Service\Serializer\NormalizerInterface;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

/**
 * Class BookingProductNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingProductNormalizer implements NormalizerInterface
{
    /**
     *
     * @param BookingProductInterface $bookingProduct
     * @param array $context
     *
     * @return array
     */
    public function normalize($bookingProduct, array $context = array())
    {
        $prices = [];
        /** @var RateInterface $rate */
        foreach ($bookingProduct->getRates() as $rate) {
            $prices[$rate->getStart()->format('Y-m-d')] = [
                "price" => $rate->getAmount(),
                "rate_id" => RatePlanCode::SBX,
            ];
        }

        return [
            "room_id" => $bookingProduct->getProduct()->getIdentifier(),
            "daily_prices" => $prices,
            "adults_number" => $bookingProduct->getTotalGuests(),
            "guests" => array_map(
                function (GuestInterface $guest) {
                    return $guest->getName();
                },
                $bookingProduct->getGuests()->toArray()
            ),
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
        throw new MethodNotImplementedException('Method BookingProduct::denormalize is not implemented');
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsNormalization(string $class): bool
    {
        return BookingProduct::class === $class;
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
