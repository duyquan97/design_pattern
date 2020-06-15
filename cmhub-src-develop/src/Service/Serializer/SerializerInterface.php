<?php

namespace App\Service\Serializer;

/**
 * Interface SerializerInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
interface SerializerInterface
{
    /**
     *
     * @param object $object
     * @param array  $context
     *
     * @return mixed
     */
    public function normalize($object, array $context = array());

    /**
     *
     * @param array|\stdClass $data
     * @param string          $class
     * @param array           $context
     *
     * @return object
     */
    public function denormalize($data, string $class, array $context = array());
}
