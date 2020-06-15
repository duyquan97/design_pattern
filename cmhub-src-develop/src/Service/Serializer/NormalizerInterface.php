<?php

namespace App\Service\Serializer;

/**
 * Interface NormalizerInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
interface NormalizerInterface
{
    /**
     *
     * @param mixed $object
     * @param array $context
     *
     * @return mixed
     */
    public function normalize($object, array $context = array());

    /**
     *
     * @param mixed $data
     * @param array $context
     *
     * @return mixed
     */
    public function denormalize($data, array $context = array());

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsNormalization(string $class): bool;

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsDenormalization(string $class): bool;
}
