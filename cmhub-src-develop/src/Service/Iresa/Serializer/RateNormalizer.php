<?php

namespace App\Service\Iresa\Serializer;

use App\Model\Factory\RateFactory;
use App\Model\Rate;
use App\Service\Serializer\NormalizerInterface;

/**
 * Class RateNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class RateNormalizer implements NormalizerInterface
{
    /**
     *
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
     * @return mixed
     */
    public function normalize($object, array $context = array())
    {
        // TODO: Implement normalize() method.
    }

    /**
     *
     * @param mixed $rate
     * @param array $context
     *
     * @return Rate
     */
    public function denormalize($rate, array $context = array())
    {
        return $this
            ->rateFactory
            ->create(
                new \DateTime($rate->dateStart),
                new \DateTime($rate->dateEnd),
                $rate->amount,
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
