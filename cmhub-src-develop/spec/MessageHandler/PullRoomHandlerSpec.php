<?php

namespace spec\App\MessageHandler;

use App\Entity\Partner;
use App\Message\PullRoom;
use App\MessageHandler\PullRoomHandler;
use App\Model\ProductCollection;
use App\Repository\PartnerRepository;
use App\Service\Iresa\IresaBookingEngine;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

/**
 * Class PullRoomHandlerSpec
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class PullRoomHandlerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PullRoomHandler::class);
    }

    function let(IresaBookingEngine $bookingEngine, PartnerRepository $partnerRepository)
    {
        $this->beConstructedWith($bookingEngine, $partnerRepository);
    }

    function it_doesnt_process_message_if_partner_not_found(PullRoom $message, PartnerRepository $partnerRepository)
    {
        $message->getPartnerId()->willReturn('id');
        $partnerRepository->findOneBy(['identifier' => 'id'])->willReturn(null);

        $this->shouldThrow(UnrecoverableMessageHandlingException::class)->during('__invoke', [$message]);
    }

    function it_process_message(PullRoom $message, PartnerRepository $partnerRepository, Partner $partner,
                                IresaBookingEngine $bookingEngine, ProductCollection $productCollection)
    {
        $message->getPartnerId()->willReturn('id');
        $partnerRepository->findOneBy(['identifier' => 'id'])->willReturn($partner);

        $bookingEngine->pullProducts($partner)->shouldBeCalled()->willReturn($productCollection);

        $this->__invoke($message);
    }

    function it_throw_excpetion_while_processing_message(PullRoom $message, PartnerRepository $partnerRepository, Partner $partner,
                                IresaBookingEngine $bookingEngine)
    {
        $message->getPartnerId()->willReturn('id');
        $partnerRepository->findOneBy(['identifier' => 'id'])->willReturn($partner);

        $exception = new ClientException('exception', new Request('GET', ''));
        $bookingEngine->pullProducts($partner)->shouldBeCalled()->willThrow($exception);

        $this->shouldThrow($exception)->during('__invoke', [$message]);
    }
}
