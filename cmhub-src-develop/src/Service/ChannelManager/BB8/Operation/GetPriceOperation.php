<?php

namespace App\Service\ChannelManager\BB8\Operation;

use App\Exception\DateFormatException;
use App\Exception\PartnerNotFoundException;
use App\Exception\ValidationException;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\BB8\Serializer\ProductRateCollectionNormalizer;
use App\Service\Loader\PartnerLoader;
use App\Service\Loader\ProductLoader;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class GetPriceOperation
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class GetPriceOperation implements BB8OperationInterface
{
    public const NAME = 'get_price';

    /**
     *
     * @var BookingEngineInterface
     */
    private $bookingEngine;

    /**
     *
     * @var ProductRateCollectionNormalizer
     */
    private $productRateCollectionNormalizer;

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
     * @var CmhubLogger
     */
    private $logger;

    /**
     *
     * GetRatesOperation constructor.
     *
     * @param BookingEngineInterface          $bookingEngine
     * @param ProductRateCollectionNormalizer $productRateCollectionNormalizer
     * @param ProductLoader                   $productLoader
     * @param PartnerLoader                   $partnerLoader
     * @param CmhubLogger                     $logger
     */
    public function __construct(BookingEngineInterface $bookingEngine, ProductRateCollectionNormalizer $productRateCollectionNormalizer, ProductLoader $productLoader, PartnerLoader $partnerLoader, CmhubLogger $logger)
    {
        $this->bookingEngine = $bookingEngine;
        $this->productRateCollectionNormalizer = $productRateCollectionNormalizer;
        $this->productLoader = $productLoader;
        $this->partnerLoader = $partnerLoader;
        $this->logger = $logger;
    }

    /**
     *
     * @param Request $request
     *
     * @return array
     *
     * @throws DateFormatException
     * @throws PartnerNotFoundException
     * @throws ValidationException
     */
    public function handle(Request $request): array
    {
        if (!$request->get('startDate') || !$request->get('endDate')) {
            throw new ValidationException('Dates must be defined');
        }

        $startDate = date_create($request->get('startDate'));
        $endDate = date_create($request->get('endDate'));
        if (!$startDate || !$endDate) {
            throw new DateFormatException('Y-m-d');
        }

        $identifiers = $request->get('externalPartnerIds');
        if (!$identifiers) {
            throw new BadRequestHttpException('Parameter \'externalPartnerIds\' is mandatory.');
        }

        $identifiers = explode(',', $identifiers);

        if (empty($identifiers)) {
            throw new BadRequestHttpException('Parameter \'externalPartnerIds\' is mandatory.');
        }

        $partners = $this->partnerLoader->findByIds($identifiers);

        if (empty($partners)) {
            throw new PartnerNotFoundException($request->get('externalPartnerIds'));
        }

        $rates = [];

        $externalRoomIds = $request->get('externalRoomIds');
        $roomCodes = explode(',', $externalRoomIds);

        foreach ($partners as $partner) {
            $externalRoomIds ?
                $products = $this
                    ->productLoader
                    ->getProductsByRoomCode(
                        $partner,
                        $roomCodes
                    ) : $products = $this->productLoader->getByPartner($partner)->getProducts();

            $rates = array_merge(
                $rates,
                $this->bookingEngine
                    ->getRates($partner, $startDate, $endDate, $products)
                    ->getProductRates()
            );
        }
        $prices = $this->productRateCollectionNormalizer->normalize($rates);

        $this->logger->addOperationInfo(LogAction::GET_PRICES, null, $this);

        return $prices;
    }

    /**
     *
     * @param string $operation
     *
     * @return bool
     */
    public function supports(string $operation): bool
    {
        return self::NAME === $operation;
    }
}
