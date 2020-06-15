<?php

namespace App\Service\EAI\Serializer;

use App\Model\ProductRate;
use App\Model\ProductRateInterface;
use App\Service\Serializer\NormalizerInterface;

/**
 * Class ProductRateNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductRateNormalizer implements NormalizerInterface
{

    /**
     *
     * @param ProductRateInterface $object
     * @param array                $context
     *
     * @return mixed
     */
    public function normalize($object, array $context = array())
    {
        $data = [];
        $productId = $object->getProduct()->getIdentifier();
        $partnerId = $object->getProduct()->getPartner()->getIdentifier();

        foreach ($object->getRates() as $rate) {
            $data[] = [
                'rateBand'  => [
                    'code'    => 'SBX',
                    'partner' => [
                        'id' => $partnerId,
                    ],
                ],
                'product'   => [
                    'id' => $productId,
                ],
                'dateFrom'  => $rate->getStart()->format('Y-m-d\\TH:i:s.P'),
                'dateTo'    => (clone $rate->getEnd())->modify('+1 day')->format('Y-m-d\\TH:i:s.P'),
                'updatedAt' => (new \DateTime())->format('Y-m-d\\TH:i:s.P'),
                'price'     => [
                    'amount'       => $rate->getAmount(),
                    'currencyCode' => 'EUR',
                ],
            ];
        }

        return $data;
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
        // TODO: Implement denormalize() method.
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsNormalization(string $class): bool
    {
        return ProductRate::class === $class;
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsDenormalization(string $class): bool
    {
        return ProductRate::class === $class;
    }
}
