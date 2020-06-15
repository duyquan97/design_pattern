<?php

namespace App\Service\ChannelManager\SoapOta\Serializer\V2015A;

use App\Model\ProductRateCollection;
use App\Model\RatePlanCode;
use App\Service\Serializer\NormalizerInterface;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

/**
 * Class ProductRateNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductRateNormalizer implements NormalizerInterface
{
    /**
     *
     * @param ProductRateCollection $object
     * @param array                 $context
     *
     * @return array
     */
    public function normalize($object, array $context = array())
    {
        $data = [
            'RatePlans' => [
                'HotelCode' => $object->getPartner()->getIdentifier(),
                'RatePlan'  => [],
            ],
        ];

        foreach ($object->getProductRates() as $productRates) {
            foreach ($productRates->getRates() as $productRate) {
                $data['RatePlans']['RatePlan'][] = [
                    'RatePlanCode' => RatePlanCode::SBX,
                    'Start'        => $productRate->getStart()->format('Y-m-d'),
                    'End'          => $productRate->getEnd()->format('Y-m-d'),
                    'Rates'        => [
                        'Rate' => [
                            [
                                'InvTypeCode'     => $productRates->getProduct()->getIdentifier(),
                                'CurrencyCode'    => $object->getPartner()->getCurrency(),
                                'BaseByGuestAmts' => [
                                    'BaseByGuestAmt' => [
                                        [
                                            'AmountAfterTax' => $productRate->getAmount(),
                                            'NumberOfGuests' => 2,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ];
            }
        }

        return $data;
    }

    /**
     *
     * @param mixed $data
     * @param array $context
     *
     * @return void
     */
    public function denormalize($data, array $context = array())
    {
        throw new MethodNotImplementedException('Method ProductRate::denormalize is not implemented');
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsNormalization(string $class): bool
    {
        if (ProductRateCollection::class === $class) {
            return true;
        }

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
        return false;
    }
}
