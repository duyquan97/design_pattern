<?php

namespace App\MessageHandler;

use App\Message\PartnerUpdated;
use App\Repository\PartnerRepository;
use App\Service\Iresa\IresaBookingEngine;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Class PartnerUpdatedHandler
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class PartnerUpdatedHandler implements MessageHandlerInterface
{
    /**
     * @var IresaBookingEngine
     */
    private $iresaBookingEngine;

    /**
     * @var PartnerRepository
     */
    private $partnerRepository;

    /**
     * PartnerUpdatedHandler constructor.
     *
     * @param IresaBookingEngine $iresaBookingEngine
     * @param PartnerRepository  $partnerRepository
     */
    public function __construct(IresaBookingEngine $iresaBookingEngine, PartnerRepository $partnerRepository)
    {
        $this->iresaBookingEngine = $iresaBookingEngine;
        $this->partnerRepository = $partnerRepository;
    }

    /**
     *
     * @param PartnerUpdated $message
     *
     * @return void
     */
    public function __invoke(PartnerUpdated $message)
    {
        $partner = $this->partnerRepository->findOneBy(['identifier' => $message->getIdentifier()]);

        if (!$partner) {
            throw new UnrecoverableMessageHandlingException(sprintf('Partner identifier `%s` has not been found in DB', $message->getIdentifier()));
        }

        $this->iresaBookingEngine->pullProducts($partner);
    }
}
