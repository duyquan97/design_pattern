<?php

namespace App\Service\ChannelManager\BB8\Operation;

use App\Exception\AccessDeniedException;
use App\Exception\CmHubException;
use App\Exception\DateFormatException;
use App\Exception\PartnerNotFoundException;
use App\Exception\ProductNotFoundException;
use App\Exception\ValidationException;
use App\Security\Voter\BB8Voter;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\BB8\Serializer\ProductRateCollectionNormalizer;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class UpdatePriceOperation
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class UpdatePriceOperation implements BB8OperationInterface
{
    public const NAME = 'update_price';

    /**
     * @var BookingEngineInterface
     */
    private $bookingEngine;

    /**
     *
     * @var CmhubLogger
     */
    private $logger;

    /**
     * @var ProductRateCollectionNormalizer
     */
    private $productRateCollectionNormalizer;

    /**
     *
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * UpdateDataOperation constructor.
     *
     * @param BookingEngineInterface $bookingEngine
     * @param CmhubLogger $logger
     * @param ProductRateCollectionNormalizer $productRateCollectionNormalizer
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(BookingEngineInterface $bookingEngine, CmhubLogger $logger, ProductRateCollectionNormalizer $productRateCollectionNormalizer, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->bookingEngine = $bookingEngine;
        $this->logger = $logger;
        $this->productRateCollectionNormalizer = $productRateCollectionNormalizer;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     *
     * @param Request $request
     *
     * @return array
     *
     * @throws DateFormatException
     * @throws ProductNotFoundException
     * @throws ValidationException
     * @throws CmHubException
     * @throws PartnerNotFoundException
     */
    public function handle(Request $request): array
    {
        $productRateCollection = $this->productRateCollectionNormalizer->denormalize(json_decode($request->getContent()));
        $partner = $productRateCollection->getPartner();

        if (!$this->authorizationChecker->isGranted(BB8Voter::BB8_OPERATION, $partner)) {
            throw new AccessDeniedException(403);
        }

        $this->bookingEngine->updateRates($productRateCollection);

        $this->logger->addOperationInfo(LogAction::UPDATE_RATES, $partner, $this);

        return [];
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
