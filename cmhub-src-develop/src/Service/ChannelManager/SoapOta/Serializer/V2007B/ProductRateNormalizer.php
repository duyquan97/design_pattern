<?php

namespace App\Service\ChannelManager\SoapOta\Serializer\V2007B;

use App\Model\ProductRateCollection;
use App\Model\ProductRateInterface;
use App\Model\Rate;
use App\Model\RateInterface;
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
                'RatePlan'  => [
                    [
                        'RatePlanCode' => RatePlanCode::SBX,
                        'Description'  => [
                            'Text' => Rate::SBX_RATE_PLAN_NAME,
                        ],
                        'Rates'        => [],
                    ],
                ],
            ],
        ];

        /** @var ProductRateInterface $productRates */
        foreach ($object->getProductRates() as $productRates) {
            /** @var RateInterface $productRate */
            foreach ($productRates->getRates() as $productRate) {
                $data['RatePlans']['RatePlan'][0]['Rates'][] = [
                    'InvTypeCode'     => $productRates->getProduct()->getIdentifier(),
                    'CurrencyCode'    => $object->getPartner()->getCurrency(),
                    'IsRoom'          => true,
                    'Start'           => $productRate->getStart()->format('Y-m-d'),
                    'End'             => $productRate->getEnd()->format('Y-m-d'),
                    'BaseByGuestAmts' => [
                        'BaseByGuestAmt' => [
                            [
                                'AmountAfterTax' => $productRate->getAmount(),
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
        return ProductRateCollection::class === $class;
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
