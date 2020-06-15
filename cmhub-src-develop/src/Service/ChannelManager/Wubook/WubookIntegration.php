<?php

namespace App\Service\ChannelManager\Wubook;

use App\Exception\AccessDeniedException;
use App\Exception\CmHubException;
use App\Exception\PartnerNotFoundException;
use App\Exception\ValidationException;
use App\Exception\WubookOperationNotFoundException;
use App\Model\PartnerInterface;
use App\Model\WubookErrorCode;
use App\Security\Voter\WubookVoter;
use App\Service\ChannelManager\Wubook\Operation\WubookOperationInterface;
use App\Service\Loader\PartnerLoader;
use App\Utils\Monolog\CmhubLogger;
use Monolog\Logger;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class WubookIntegration
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class WubookIntegration
{

    /**
     * @var WubookOperationInterface[]
     */
    private $operations;

    /**
     *
     * @var CmhubLogger
     */
    private $logger;

    /**
     *
     * @var PartnerLoader
     */
    private $partnerLoader;

    /**
     *
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * WubookIntegration constructor.
     *
     * @param array                         $operations
     * @param CmhubLogger                   $logger
     * @param PartnerLoader                 $partnerLoader
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(array $operations, CmhubLogger $logger, PartnerLoader $partnerLoader, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->operations = $operations;
        $this->logger = $logger;
        $this->partnerLoader = $partnerLoader;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param \StdClass $request
     *
     * @return array
     *
     * @throws CmhubException
     * @throws WubookOperationNotFoundException
     */
    public function handle(\StdClass $request)
    {
        $hotelId = $request->hotel_auth->hotel_id;
        $partner = $this->partnerLoader->find($hotelId);

        if (!$partner instanceof PartnerInterface) {
            throw new PartnerNotFoundException($hotelId, WubookErrorCode::PARTNER_NOT_FOUND);
        }

        if (!$this->authorizationChecker->isGranted(WubookVoter::WUBOOK_OPERATION, $partner)) {
            throw new AccessDeniedException(WubookErrorCode::PARTNER_NOT_AUTHORIZED);
        }

        foreach ($this->operations as $operation) {
            if ($operation->supports($request->action)) {
                return $operation->handle($request, $partner);
            }
        }

        $this->logger->addRecord(
            Logger::ALERT,
            sprintf('Wubook Operation %s not found', $request->action),
            [],
            $this
        );

        throw new WubookOperationNotFoundException();
    }
}
