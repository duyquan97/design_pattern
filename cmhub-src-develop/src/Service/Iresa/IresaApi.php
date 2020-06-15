<?php

namespace App\Service\Iresa;

use App\Exception\IresaClientException;
use App\Exception\NormalizerNotFoundException;
use App\Model\PartnerInterface;
use App\Model\ProductAvailabilityCollectionInterface;
use App\Model\ProductInterface;
use App\Model\ProductRateCollectionInterface;
use App\Model\ProductRateInterface;
use App\Service\Iresa\Serializer\IresaSerializer;

/**
 * Class IresaApi
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class IresaApi
{
    const GET_AVAILABILITIES = '/apipro/ReadAvailabilities/';
    const UPDATE_AVAILABILITIES = '/apipro/UpdateAvailabilities/';
    const GET_RATES = '/apipro/ReadRates/';
    const UPDATE_RATES = '/apipro/UpdateRates/';
    const GET_BOOKINGS = '/apipro/ReadBookings/';
    const GET_PRODUCTS = '/apipro/GetMainAccomodations/';

    /**
     *
     * @var IresaClient
     */
    private $iresaClient;

    /**
     *
     * @var IresaSerializer
     */
    private $iresaSerializer;

    /**
     *
     * @var string
     */
    private $iresaDefaultLanguageCode;

    /**
     *
     * IresaApi constructor.
     *
     * @param IresaClient     $iresaClient
     * @param IresaSerializer $iresaSerializer
     * @param string $iresaDefaultLanguageCode
     */
    public function __construct(IresaClient $iresaClient, IresaSerializer $iresaSerializer, string $iresaDefaultLanguageCode)
    {
        $this->iresaClient = $iresaClient;
        $this->iresaSerializer = $iresaSerializer;
        $this->iresaDefaultLanguageCode = $iresaDefaultLanguageCode;
    }

    /**
     *
     * @param PartnerInterface $partner
     * @param \DateTime        $start
     * @param \DateTime        $end
     * @param array            $products
     *
     * @return mixed
     *
     * @throws IresaClientException
     */
    public function getAvailabilities(PartnerInterface $partner, \DateTime $start, \DateTime $end, array $products = array())
    {
        $response = $this
            ->iresaClient
            ->fetch(
                static::GET_AVAILABILITIES,
                [
                    'partnerCode'    => $partner->getIdentifier(),
                    'dateStart'      => $start->format('c'),
                    'dateEnd'        => (clone $end)->modify('+1 day')->format('c'),
                    'allProductType' => false,
                    'roomTypes'      => array_map(
                        function (ProductInterface $product) {
                            return [
                                'roomTypeCode' => $product->getIdentifier(),
                            ];
                        },
                        $products
                    ),
                ]
            );

        return $response->data->roomTypes;
    }

    /**
     *
     * @param ProductAvailabilityCollectionInterface $availabilityCollection
     *
     * @return ProductAvailabilityCollectionInterface
     *
     * @throws IresaClientException
     * @throws NormalizerNotFoundException
     */
    public function updateAvailabilities(ProductAvailabilityCollectionInterface $availabilityCollection): ProductAvailabilityCollectionInterface
    {
        $this
            ->iresaClient
            ->fetch(
                static::UPDATE_AVAILABILITIES,
                $this->iresaSerializer->normalize($availabilityCollection)
            );

        return $availabilityCollection;
    }

    /**
     *
     * @param PartnerInterface $partner
     * @param \DateTime        $start
     * @param \DateTime        $end
     * @param array            $products
     *
     * @return array
     *
     * @throws IresaClientException
     */
    public function getRates(PartnerInterface $partner, \DateTime $start, \DateTime $end, array $products = array()): array
    {
        $response = $this
            ->iresaClient
            ->fetch(
                static::GET_RATES,
                [
                    'partnerCode'    => $partner->getIdentifier(),
                    'dateStart'      => $start->format('c'),
                    'dateEnd'        => (clone $end)->modify('+1 day')->format('c'),
                    'allProductType' => false,
                    'roomTypes'      => array_map(
                        function (ProductInterface $product) {
                            return [
                                'roomTypeCode' => $product->getIdentifier(),
                            ];
                        },
                        $products
                    ),
                ]
            );

        return $response->data->roomTypes;
    }

    /**
     *
     * @param ProductRateCollectionInterface $productRates
     *
     * @return ProductRateCollectionInterface
     *
     * @throws IresaClientException
     */
    public function updateRates(ProductRateCollectionInterface $productRates): ProductRateCollectionInterface
    {
        $roomTypes = [];
        /* @var ProductRateInterface $productRate */
        foreach ($productRates->getProductRates() as $productRate) {
            $roomType = [
                'roomTypeCode' => $productRate->getProduct()->getIdentifier(),
                'currency'     => $productRates->getPartner()->getCurrency(),
                'roomTypes'    => [],
            ];

            foreach ($productRate->getRates() as $rate) {
                $roomType['roomTypes'][] = [
                    'dateStart' => $rate->getStart()->format('Y-m-d'),
                    'dateEnd'   => (clone $rate->getEnd())->modify('+1 day')->format('Y-m-d'),
                    'amount'    => $rate->getAmount(),
                ];
            }

            $roomTypes[] = $roomType;
        }

        $this
            ->iresaClient
            ->fetch(
                static::UPDATE_RATES,
                [
                    'partnerCode' => $productRates->getPartner()->getIdentifier(),
                    'rates'       => $roomTypes,
                ]
            );

        return $productRates;
    }

    /**
     *
     * @param PartnerInterface $partner
     * @param \DateTime        $start
     * @param \DateTime        $end
     *
     * @return mixed
     *
     * @throws IresaClientException
     */
    public function getBookings(PartnerInterface $partner, \DateTime $start, \DateTime $end)
    {
        $response = $this
            ->iresaClient
            ->fetch(
                static::GET_BOOKINGS,
                [
                    'partnerCode'           => $partner->getIdentifier(),
                    'dateStartLastModified' => $start->format('Y-m-d'),
                    'dateEndLastModified'   => (clone $end)->modify('+1 day')->format('Y-m-d'),
                    'allProductType'        => false,
                ]
            );

        return $response->data->bookings;
    }

    /**
     *
     * @param PartnerInterface $partner
     *
     * @return mixed
     *
     * @throws IresaClientException
     */
    public function getProducts(PartnerInterface $partner)
    {
        $response = $this
            ->iresaClient
            ->fetch(
                static::GET_PRODUCTS,
                [
                    'partnerCode' => $partner->getIdentifier(),
                    'langCode' => $this->iresaDefaultLanguageCode,
                ]
            );

        return $response->data->listRoom;
    }
}
