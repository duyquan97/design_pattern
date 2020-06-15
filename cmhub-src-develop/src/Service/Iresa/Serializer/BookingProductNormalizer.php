<?php

namespace App\Service\Iresa\Serializer;

use App\Entity\Partner;
use App\Exception\ProductNotFoundException;
use App\Model\BookingProduct;
use App\Model\Factory\BookingProductFactory;
use App\Model\Guest;
use App\Model\Rate;
use App\Service\Loader\ProductLoader;
use App\Service\Serializer\NormalizerInterface;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogKey;
use App\Utils\Monolog\LogType;

/**
 * Class BookingProductNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingProductNormalizer implements NormalizerInterface
{
    /**
     *
     * @var BookingProductFactory
     */
    private $bookingProductFactory;

    /**
     *
     * @var GuestNormalizer
     */
    private $guestNormalizer;

    /**
     * @var RateNormalizer
     */
    private $rateNormalizer;

    /**
     *
     * @var ProductLoader
     */
    private $productLoader;

    /**
     *
     * @var CmhubLogger
     */
    private $logger;

    /**
     * BookingProductNormalizer constructor.
     *
     * @param BookingProductFactory $bookingProductFactory
     * @param GuestNormalizer       $guestNormalizer
     * @param RateNormalizer        $rateNormalizer
     * @param ProductLoader         $productLoader
     * @param CmhubLogger           $logger
     */
    public function __construct(BookingProductFactory $bookingProductFactory, GuestNormalizer $guestNormalizer, RateNormalizer $rateNormalizer, ProductLoader $productLoader, CmhubLogger $logger)
    {
        $this->bookingProductFactory = $bookingProductFactory;
        $this->guestNormalizer = $guestNormalizer;
        $this->rateNormalizer = $rateNormalizer;
        $this->productLoader = $productLoader;
        $this->logger = $logger;
    }

    /**
     *
     * @param mixed $object
     * @param array $context
     *
     * @return mixed
     */
    public function normalize($object, array $context = array())
    {
        // TODO: Implement normalize() method.
    }

    /**
     *
     * @param mixed $data
     * @param array $context
     *
     * @return BookingProduct
     */
    public function denormalize($data, array $context = array())
    {
        /* @var Partner $partner */
        $partner = $context['partner'];

        $product = $this->productLoader->find($partner, $data->roomTypeCode, []); // Retrieve child products too
        if (!$product) {
            $exception = new ProductNotFoundException($partner, $data->roomTypeCode);
            $this->logger->addRecord(
                \Monolog\Logger::INFO,
                $exception->getMessage(),
                [
                    LogKey::TYPE_KEY     => LogType::CMHUB_EXCEPTION_TYPE,
                    LogKey::EX_TYPE_KEY  => $exception->getExceptionType(),
                    LogKey::MESSAGE_KEY  => $exception->getMessage(),
                    LogKey::USERNAME_KEY => $partner->getUsername(),
                    LogKey::CM_KEY       => ($partner->getChannelManager()) ? $partner->getChannelManager()->getIdentifier() : '',
                ],
                $this
            );

            return null;
        }

        $bookingProduct = $this->bookingProductFactory->create($product);
        $bookingProduct
            ->setAmount($data->totalAmount)
            ->setCurrency($partner->getCurrency());

        foreach ($data->guests as $guest) {
            /* @var Guest $guestModel */
            $guestModel = $this->guestNormalizer->denormalize($guest);
            $bookingProduct->addGuest($guestModel);
        }

        foreach ($data->rates as $rate) {
            /* @var Rate $rateModel */
            $rateModel = $this->rateNormalizer->denormalize($rate, ['product' => $product]);
            $bookingProduct->addRate($rateModel);
        }

        return $bookingProduct;
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
        return BookingProduct::class === $class;
    }
}
