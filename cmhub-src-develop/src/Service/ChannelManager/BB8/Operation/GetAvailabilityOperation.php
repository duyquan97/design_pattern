<?php

namespace App\Service\ChannelManager\BB8\Operation;

use App\Exception\PartnerNotFoundException;
use App\Exception\ValidationException;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\BB8\Serializer\AvailabilityCollectionNormalizer;
use App\Service\Loader\PartnerLoader;
use App\Service\Loader\ProductLoader;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class GetAvailabilityOperation
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class GetAvailabilityOperation implements BB8OperationInterface
{
    public const NAME = 'get_availability';

    /**
     * @var BookingEngineInterface
     */
    private $bookingEngine;

    /**
     * @var AvailabilityCollectionNormalizer
     */
    private $availabilityCollectionNormalizer;

    /**
     *
     * @var CmhubLogger
     */
    private $logger;

    /**
     * @var PartnerLoader
     */
    private $partnerLoader;

    /**
     * @var ProductLoader
     */
    private $productLoader;

    /**
     * GetBookingsOperation constructor.
     *
     * @param BookingEngineInterface           $bookingEngine
     * @param AvailabilityCollectionNormalizer $availabilityCollectionNormalizer
     * @param CmhubLogger                      $logger
     * @param PartnerLoader                    $partnerLoader
     * @param ProductLoader                    $productLoader
     */
    public function __construct(BookingEngineInterface $bookingEngine, AvailabilityCollectionNormalizer $availabilityCollectionNormalizer, CmhubLogger $logger, PartnerLoader $partnerLoader, ProductLoader $productLoader)
    {
        $this->bookingEngine = $bookingEngine;
        $this->availabilityCollectionNormalizer = $availabilityCollectionNormalizer;
        $this->logger = $logger;
        $this->partnerLoader = $partnerLoader;
        $this->productLoader = $productLoader;
    }

    /**
     *
     * @param Request $request
     *
     * @return array
     *
     * @throws ValidationException
     * @throws BadRequestHttpException
     * @throws PartnerNotFoundException
     */
    public function handle(Request $request): array
    {
        if (!$request->get('startDate') || !$request->get('endDate')) {
            throw new ValidationException('Dates must be defined');
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

        $startDate = new \DateTime($request->get('startDate'));
        $endDate = new \DateTime($request->get('endDate'));

        $availabilities = [];

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

            $availabilities = array_merge(
                $availabilities,
                $this->bookingEngine
                    ->getAvailabilities($partner, $startDate, $endDate, $products)
                    ->getProductAvailabilities()
            );
        }

        $this->logger->addOperationInfo(LogAction::GET_AVAILABILITY, null, $this);

        return $this->availabilityCollectionNormalizer->normalize($availabilities);
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
