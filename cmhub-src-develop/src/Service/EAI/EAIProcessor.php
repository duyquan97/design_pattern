<?php

namespace App\Service\EAI;

use App\Exception\EAIClientException;
use App\Exception\NormalizerNotFoundException;
use App\Model\ProductAvailabilityCollectionInterface;
use App\Model\ProductRateCollectionInterface;
use App\Service\EAI\Serializer\EAISerializer;

/**
 * Class EAIProcessor
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class EAIProcessor
{
    private const UPDATE_AVAILABILITIES = '/send/channel_room_availability';
    private const UPDATE_RATES = '/send/channel_room_price';

    /**
     * @var EAIClient
     */
    private $eaiClient;

    /**
     * @var EAISerializer
     */
    private $eaiSerializer;

    /**
     * EAIProcessor constructor.
     *
     * @param EAIClient     $eaiClient
     * @param EAISerializer $eaiSerializer
     */
    public function __construct(EAIClient $eaiClient, EAISerializer $eaiSerializer)
    {
        $this->eaiClient = $eaiClient;
        $this->eaiSerializer = $eaiSerializer;
    }

    /**
     *
     * @param ProductAvailabilityCollectionInterface $availabilityCollection
     *
     * @return EAIResponse
     *
     * @throws NormalizerNotFoundException
     * @throws EAIClientException
     */
    public function updateAvailabilities(ProductAvailabilityCollectionInterface $availabilityCollection): EAIResponse
    {
        return $this
            ->eaiClient
            ->request(
                static::UPDATE_AVAILABILITIES,
                $this->eaiSerializer->normalize($availabilityCollection)
            );
    }

    /**
     *
     * @param ProductRateCollectionInterface $productRates
     *
     * @return EAIResponse
     *
     * @throws EAIClientException
     * @throws NormalizerNotFoundException
     */
    public function updateRates(ProductRateCollectionInterface $productRates): EAIResponse
    {
        return $this
            ->eaiClient
            ->request(
                static::UPDATE_RATES,
                $this->eaiSerializer->normalize($productRates)
            );
    }
}
