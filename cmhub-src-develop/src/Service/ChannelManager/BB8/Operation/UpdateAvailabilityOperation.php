<?php

namespace App\Service\ChannelManager\BB8\Operation;

use App\Exception\AccessDeniedException;
use App\Exception\PartnerNotFoundException;
use App\Exception\ProductNotFoundException;
use App\Model\WubookErrorCode;
use App\Security\Voter\BB8Voter;
use App\Security\Voter\WubookVoter;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\BB8\Serializer\AvailabilityCollectionNormalizer;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class UpdateAvailabilityOperation
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class UpdateAvailabilityOperation implements BB8OperationInterface
{
    public const NAME = 'update_availability';

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
     * @var AvailabilityCollectionNormalizer
     */
    private $availabilityCollectionNormalizer;

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
     * @param AvailabilityCollectionNormalizer $availabilityCollectionNormalizer
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(BookingEngineInterface $bookingEngine, CmhubLogger $logger, AvailabilityCollectionNormalizer $availabilityCollectionNormalizer, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->bookingEngine = $bookingEngine;
        $this->logger = $logger;
        $this->availabilityCollectionNormalizer = $availabilityCollectionNormalizer;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     *
     * @param Request $request
     *
     * @return array
     *
     * @throws PartnerNotFoundException
     * @throws ProductNotFoundException
     * @throws AccessDeniedException
     */
    public function handle(Request $request): array
    {
        $data = json_decode($request->getContent());
        $availabilitiesCollection = $this->availabilityCollectionNormalizer->denormalize($data);
        $partner = $availabilitiesCollection->getPartner();

        if (!$this->authorizationChecker->isGranted(BB8Voter::BB8_OPERATION, $partner)) {
            throw new AccessDeniedException(403);
        }

        $this->bookingEngine->updateAvailability($availabilitiesCollection)->getProductAvailabilities();

        $this->logger->addOperationInfo(LogAction::UPDATE_AVAILABILITY, $partner, $this);

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
