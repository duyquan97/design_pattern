<?php

namespace App\Service\Iresa\Serializer;

use App\Model\Availability;
use App\Model\Factory\AvailabilityFactory;
use App\Service\Serializer\NormalizerInterface;

/**
 * Class AvailabilityNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class AvailabilityNormalizer implements NormalizerInterface
{
    /**
     *
     * @var AvailabilityFactory
     */
    private $availabilityFactory;

    /**
     * AvailabilityNormalizer constructor.
     *
     * @param AvailabilityFactory $availabilityFactory
     */
    public function __construct(AvailabilityFactory $availabilityFactory)
    {
        $this->availabilityFactory = $availabilityFactory;
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
     * @param mixed $data
     * @param array $context
     *
     * @return mixed
     */
    public function denormalize($data, array $context = array())
    {
        return $this
            ->availabilityFactory
            ->create(
                new \DateTime($data->date),
                new \DateTime($data->date),
                isset($data->stock) ? $data->stock : 0,
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
        return $class === Availability::class;
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsDenormalization(string $class): bool
    {
        return $class === Availability::class;
    }
}
