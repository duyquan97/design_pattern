<?php

namespace App\Service\ChannelManager\BB8\Serializer;

use App\Exception\CmHubException;
use App\Exception\DateFormatException;
use App\Exception\PartnerNotFoundException;
use App\Exception\ProductNotFoundException;
use App\Exception\ValidationException;
use App\Model\Factory\ProductRateCollectionFactory;
use App\Model\Factory\RateFactory;
use App\Model\ProductRate;
use App\Model\ProductRateCollection;
use App\Model\RatePlanCode;
use App\Service\Loader\PartnerLoader;
use App\Service\Loader\ProductLoader;
use App\Service\Serializer\NormalizerInterface;

/**
 * Class ProductRateCollectionNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductRateCollectionNormalizer implements NormalizerInterface
{
    /**
     * @var ProductRateCollectionFactory
     */
    private $productRateCollectionFactory;

    /**
     * @var RateFactory
     */
    private $rateFactory;

    /**
     * @var PartnerLoader
     */
    private $partnerLoader;

    /**
     * @var ProductLoader
     */
    private $productLoader;

    /**
     * ProductRateCollectionNormalizer constructor.
     *
     * @param ProductRateCollectionFactory $productRateCollectionFactory
     * @param RateFactory                  $rateFactory
     * @param PartnerLoader                $partnerLoader
     * @param ProductLoader                $productLoader
     */
    public function __construct(ProductRateCollectionFactory $productRateCollectionFactory, RateFactory $rateFactory, PartnerLoader $partnerLoader, ProductLoader $productLoader)
    {
        $this->productRateCollectionFactory = $productRateCollectionFactory;
        $this->rateFactory = $rateFactory;
        $this->partnerLoader = $partnerLoader;
        $this->productLoader = $productLoader;
    }


    /**
     *
     * @param ProductRate[] $productRates
     * @param array         $context
     *
     * @return mixed
     */
    public function normalize($productRates, array $context = array())
    {
        $data = [];

        /** @var ProductRate $productRate */
        foreach ($productRates as $productRate) {
            foreach ($productRate->getRates() as $rate) {
                $data[] = [
                    'currencyCode'      => $productRate->getProduct()->getPartner()->getCurrency(),
                    'date'              => $rate->getStart()->format('Y-m-d'),
                    'amount'            => $rate->getAmount()*100,
                    'rateBandCode'      => RatePlanCode::SBX,
                    'externalPartnerId' => $productRate->getProduct()->getPartner()->getIdentifier(),
                    'externalRoomId'    => $productRate->getProduct()->getIdentifier(),
                    'externalCreatedAt' => $productRate->getProduct()->getCreatedAt()->format('Y-m-d\TH:i:sP'),
                    'externalUpdatedAt' => $productRate->getProduct()->getUpdatedAt()->format('Y-m-d\TH:i:sP'),
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
     * @return ProductRateCollection
     *
     * @throws CmHubException
     * @throws DateFormatException
     * @throws PartnerNotFoundException
     * @throws ProductNotFoundException
     * @throws ValidationException
     */
    public function denormalize($data, array $context = array())
    {
        if (empty($data) || !is_array($data)) {
            throw new CmHubException('Request is empty');
        }

        if (!isset($data[0]->externalPartnerId)) {
            throw new CmHubException('Missing `externalPartnerId`');
        }

        $partner = $this->partnerLoader->find($data[0]->externalPartnerId);

        if (!$partner) {
            throw new PartnerNotFoundException($data[0]->externalPartnerId);
        }

        $collection = $this->productRateCollectionFactory->create($partner);
        foreach ($data as $rateData) {
            $product = $this->productLoader->find($partner, $rateData->externalRoomId);

            if (!$product) {
                throw new ProductNotFoundException($partner, $rateData->externalRoomId);
            }

            $date = \DateTime::createFromFormat('Y-m-d', $rateData->date);
            $startDate = $date;
            $endDate = $date;

            if (!$date) {
                throw new DateFormatException('Y-m-d');
            }

            $amount = $rateData->amount / 100;
            if ($amount < 0) {
                throw new ValidationException(sprintf('The price must be greater or equals to 0. Negative value `%s` has been provided.', $amount));
            }

            if ($amount > 99999) {
                throw new ValidationException('The maximum price value allowed is 99999');
            }

            $rate = $this
                ->rateFactory
                ->create(
                    $startDate,
                    $endDate,
                    $amount,
                    $product
                );

            $collection->addRate($product, $rate);
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
