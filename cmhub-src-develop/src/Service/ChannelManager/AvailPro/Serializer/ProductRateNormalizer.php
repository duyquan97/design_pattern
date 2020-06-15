<?php

namespace App\Service\ChannelManager\AvailPro\Serializer;

use App\Exception\DateFormatException;
use App\Exception\PartnerNotFoundException;
use App\Exception\ProductNotFoundException;
use App\Exception\ValidationException;
use App\Model\Factory\ProductRateCollectionFactory;
use App\Model\Factory\RateFactory;
use App\Model\ProductInterface;
use App\Model\ProductRateCollection;
use App\Model\Rate;
use App\Model\RatePlanCode;
use App\Service\ChannelManager\AvailPro\AvailProChannelManager;
use App\Service\Loader\PartnerLoader;
use App\Service\Loader\ProductLoader;
use App\Service\Serializer\NormalizerInterface;
use Symfony\Component\Intl\Exception\NotImplementedException;

/**
 * Class ProductRateNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductRateNormalizer implements NormalizerInterface
{
    /**
     *
     * @var ProductLoader
     */
    private $productLoader;

    /**
     *
     * @var PartnerLoader
     */
    private $partnerLoader;

    /**
     *
     * @var RateFactory
     */
    private $rateFactory;

    /**
     * @var ProductRateCollectionFactory
     */
    private $productRateCollectionFactory;

    /**
     * ProductRateNormalizer constructor.
     *
     * @param ProductLoader                $productLoader
     * @param PartnerLoader                $partnerLoader
     * @param RateFactory                  $rateFactory
     * @param ProductRateCollectionFactory $productRateCollectionFactory
     */
    public function __construct(ProductLoader $productLoader, PartnerLoader $partnerLoader, RateFactory $rateFactory, ProductRateCollectionFactory $productRateCollectionFactory)
    {
        $this->productLoader = $productLoader;
        $this->partnerLoader = $partnerLoader;
        $this->rateFactory = $rateFactory;
        $this->productRateCollectionFactory = $productRateCollectionFactory;
    }

    /**
     *
     * @param mixed $object
     * @param array $context
     *
     * @return void
     */
    public function normalize($object, array $context = array())
    {
        throw new NotImplementedException('Method AvailPro/ProductRateCollection::normalize is not implemented');
    }

    /**
     *
     * @param mixed $data
     * @param array $context
     *
     * @return ProductRateCollection
     *
     * @throws DateFormatException
     * @throws PartnerNotFoundException
     * @throws ProductNotFoundException
     * @throws ValidationException
     */
    public function denormalize($data, array $context = array()): ProductRateCollection
    {
        $partner = $this->partnerLoader->find($partnerId = $data->inventoryUpdate->{'@attributes'}->hotelId);
        if (!$partner) {
            throw new PartnerNotFoundException($partnerId);
        }

        if ($partner->getChannelManager() && $partner->getChannelManager()->getIdentifier() !== AvailProChannelManager::NAME) {
            throw new PartnerNotFoundException($partnerId);
        }

        $collection = $this->productRateCollectionFactory->create($partner);
        foreach ($data->inventoryUpdate->room as $room) {
            $product = $this->productLoader->find($partner, $roomId = $room->{'@attributes'}->id);
            if (!$product) {
                throw new ProductNotFoundException($partner, $roomId);
            }

            $room = $this->normalizeAttributeArray($room, 'rate');

            foreach ($room->rate as $rate) {
                $rate = $this->normalizeAttributeArray($rate, 'planning');

                $collection = $this->buildRates($rate, $product, $collection);
            }
        }

        return $collection;
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
        if (ProductRateCollection::class === $class) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param \stdClass   $request
     * @param null|string $key
     *
     * @return array|\stdClass
     */
    private function normalizeAttributeArray(\stdClass $request, ?string $key = null)
    {
        if ($key) {
            if (!isset($request->{$key})) {
                return (object) [
                    'rate' => [],
                ];
            }

            if (!is_array($request->{$key})) {
                $request->{$key} = [$request->{$key}];

                return $request;
            }

            return $request;
        }

        if (!is_array($request)) {
            return [$request];
        }
    }

    /**
     *
     * @param \stdClass             $rate
     * @param ProductInterface      $product
     * @param ProductRateCollection $collection
     *
     * @return ProductRateCollection
     *
     * @throws DateFormatException
     * @throws ValidationException
     */
    private function buildRates($rate, ProductInterface $product, ProductRateCollection $collection): ProductRateCollection
    {
        foreach ($rate->planning as $ratePlanning) {
            $rateModel = null;

            if ($attributes = $rate->{'@attributes'}) {
                $rateData = $ratePlanning->{'@attributes'};
                if (!isset($rateData->unitPrice) || empty($rateData->unitPrice)) {
                    continue;
                }

                $rateModel = $this->createRate($attributes, $rateData, $product);
                $collection->addRate($product, $rateModel);
                continue;
            }

            throw new ValidationException('');
        }

        return $collection;
    }

    /**
     *
     * @param \stdClass        $attributes
     * @param \stdClass        $ratePlan
     * @param ProductInterface $product
     *
     * @return Rate
     *
     * @throws DateFormatException
     * @throws ValidationException
     */
    private function createRate($attributes, $ratePlan, ProductInterface $product): Rate
    {
        if (!in_array($ratePlanCode = $attributes->rateCode, RatePlanCode::RATE_PLAN_CODES)) {
            throw new ValidationException(sprintf('The rate plan code provided `%s` is not valid', $ratePlanCode));
        }

        if (!$unitPrice = $ratePlan->unitPrice) {
            throw new ValidationException('Validation Exception: Missing unit price value');
        }

        if ($unitPrice < 0) {
            throw new ValidationException(sprintf('Validation Exception: The unit price provided `%s` should be > 0', $unitPrice));
        }

        $startDate = \DateTime::createFromFormat('Y-m-d', $ratePlan->from);
        $endDate = \DateTime::createFromFormat('Y-m-d', $ratePlan->to);
        if (!$startDate || !$endDate) {
            throw new DateFormatException('Y-m-d');
        }

        return $this->rateFactory->create($startDate, $endDate, $unitPrice, $product);
    }
}
