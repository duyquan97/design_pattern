<?php

namespace App\MessageHandler;

use App\Message\PullRoom;
use App\Model\PartnerInterface;
use App\Repository\PartnerRepository;
use App\Service\Iresa\IresaBookingEngine;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Class PullRoomHandler
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class PullRoomHandler implements MessageHandlerInterface
{
    /**
     * @var IresaBookingEngine
     */
    private $bookingEngine;

    /**
     * @var PartnerRepository
     */
    private $partnerRepository;

    /**
     * PullRoomHandler constructor.
     *
     * @param IresaBookingEngine $bookingEngine
     * @param PartnerRepository $partnerRepository
     */
    public function __construct(IresaBookingEngine $bookingEngine, PartnerRepository $partnerRepository)
    {
        $this->bookingEngine = $bookingEngine;
        $this->partnerRepository = $partnerRepository;
    }

    /**
     *
     * @param PullRoom $message
     *
     * @return void
     *
     * @throws GuzzleException
     */
    public function __invoke(PullRoom $message)
    {
        /** @var PartnerInterface $partner */
        $partner = $this->partnerRepository->findOneBy(['identifier' => $message->getPartnerId()]);

        if (!$partner) {
            throw new UnrecoverableMessageHandlingException(
                sprintf('Partner with id "%s" is not found', $message->getPartnerId())
            );
        }

        $this->bookingEngine->pullProducts($partner);
    }
}
