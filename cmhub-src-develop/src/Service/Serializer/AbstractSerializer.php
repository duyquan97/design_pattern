<?php

namespace App\Service\Serializer;

use App\Exception\NormalizerNotFoundException;

/**
 * Class AbstractSerializer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
abstract class AbstractSerializer implements SerializerInterface
{
    /**
     *
     * @var NormalizerInterface[]
     */
    protected $normalizers;

    /**
     * AbstractSerializer constructor.
     *
     * @param NormalizerInterface[] $normalizers
     */
    public function __construct(array $normalizers = [])
    {
        $this->normalizers = $normalizers;
    }

    /**
     *
     * @param object $object
     * @param array  $context
     *
     * @return mixed
     *
     * @throws NormalizerNotFoundException
     */
    public function normalize($object, array $context = array())
    {
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer->supportsNormalization(get_class($object))) {
                return $normalizer->normalize($object, $context);
            }
        }

        throw new NormalizerNotFoundException();
    }

    /**
     *
     * @param array|\stdClass $data
     * @param string          $class
     * @param array           $context
     *
     * @return mixed
     *
     * @throws NormalizerNotFoundException
     */
    public function denormalize($data, string $class, array $context = array())
    {
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer->supportsDenormalization($class)) {
                return $normalizer->denormalize($data, $context);
            }
        }

        throw new NormalizerNotFoundException();
    }
}
