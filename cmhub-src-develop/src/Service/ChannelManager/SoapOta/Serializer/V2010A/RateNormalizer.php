<?php

namespace App\Service\ChannelManager\SoapOta\Serializer\V2010A;

use App\Exception\ValidationException;
use App\Model\Factory\RateFactory;
use App\Model\Rate;
use App\Service\Serializer\NormalizerInterface;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

/**
 * Class RateNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class RateNormalizer implements NormalizerInterface
{
    /**
     * @var RateFactory
     */
    private $rateFactory;

    /**
     * RateNormalizer constructor.
     *
     * @param RateFactory $rateFactory
     */
    public function __construct(RateFactory $rateFactory)
    {
        $this->rateFactory = $rateFactory;
    }

    /**
     *
     * @param mixed $object
     * @param array $context
     *
     * @return mixed|void
     */
    public function normalize($object, array $context = array())
    {
        throw new MethodNotImplementedException('Method Rate::normalize is not implemented');
    }

    /**
     *
     * @param mixed $rate
     * @param array $context
     *
     * @return Rate
     *
     * @throws ValidationException
     */
    public function denormalize($rate, array $context = array())
    {
        $amount = floatval($rate->BaseByGuestAmts->BaseByGuestAmt->AmountAfterTax);
        if ($amount < 0) {
            throw new ValidationException('The amount cannot be negative');
        }

        return $this->rateFactory
            ->create(
                $context['startDate'],
                $context['endDate'],
                $amount,
                $context['product']
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
        return Rate::class === $class;
    }
}
