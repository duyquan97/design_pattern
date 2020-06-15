<?php

namespace spec\App\MessageHandler;

use App\Entity\Partner;
use App\Message\PartnerUpdated;
use App\MessageHandler\PartnerUpdatedHandler;
use App\Repository\PartnerRepository;
use App\Service\Iresa\IresaBookingEngine;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

class PartnerUpdatedHandlerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PartnerUpdatedHandler::class);
    }

    function let(IresaBookingEngine $iresaBookingEngine, PartnerRepository $partnerRepository)
    {
        $this->beConstructedWith($iresaBookingEngine, $partnerRepository);
    }

    function it_handles_message(IresaBookingEngine $iresaBookingEngine, Partner $partner, PartnerUpdated $message, PartnerRepository $partnerRepository)
    {
        $message->getIdentifier()->willReturn('identifier');
        $partnerRepository->findOneBy(['identifier' => 'identifier'])->willReturn($partner);

        $iresaBookingEngine->pullProducts($partner)->shouldBeCalled();

        $this->__invoke($message);
    }

    function it_throws_exception_if_partner_not_found(IresaBookingEngine $iresaBookingEngine, Partner $partner, PartnerUpdated $message, PartnerRepository $partnerRepository)
    {
        $message->getIdentifier()->willReturn('identifier');
        $partnerRepository->findOneBy(['identifier' => 'identifier'])->willReturn();

        $iresaBookingEngine->pullProducts($partner)->shouldNotBeCalled();

        $this->shouldThrow(UnrecoverableMessageHandlingException::class)->during('__invoke', [$message]);
    }
}
