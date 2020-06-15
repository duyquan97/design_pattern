<?php

namespace App\Service\ChannelManager\Wubook\Serializer;

use App\Exception\DateFormatException;
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
     * @return void
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
     * @throws DateFormatException
     * @throws ValidationException
     */
    public function denormalize($rate, array $context = array())
    {
        if (!isset($rate->dfrom) || !isset($rate->dto)) {
            throw new ValidationException('Dates must be defined');
        }

        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $rate->dfrom)) {
            throw new DateFormatException('Y-m-d');
        }

        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $rate->dto)) {
            throw new DateFormatException('Y-m-d');
        }

        $startDate = \DateTime::createFromFormat('Y-m-d', $rate->dfrom);
        $endDate = \DateTime::createFromFormat('Y-m-d', $rate->dto);

        if ($startDate > $endDate) {
            throw new ValidationException("Start date cannot be greater than end date");
        }

        if (!isset($rate->price)) {
            throw new ValidationException("Price can't be an empty value");
        }

        $amount = $rate->price;

        if ($amount < 0) {
            throw new ValidationException("Amount cannot be less than 0");
        }

        return $this
            ->rateFactory
            ->create(
                $startDate,
                $endDate,
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
