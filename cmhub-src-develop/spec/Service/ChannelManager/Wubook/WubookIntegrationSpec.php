<?php

namespace spec\App\Service\ChannelManager\Wubook;

use App\Entity\Partner;
use App\Exception\AccessDeniedException;
use App\Exception\PartnerNotFoundException;
use App\Exception\ValidationException;
use App\Exception\WubookOperationNotFoundException;
use App\Security\Voter\WubookVoter;
use App\Service\ChannelManager\Wubook\WubookIntegration;
use App\Service\ChannelManager\Wubook\Operation\WubookOperationInterface;
use App\Service\Loader\PartnerLoader;
use App\Utils\Monolog\CmhubLogger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class WubookIntegrationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(WubookIntegration::class);
    }

    function let(WubookOperationInterface $operation, WubookOperationInterface $operation1, CmhubLogger $logger, PartnerLoader $partnerLoader, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->beConstructedWith([$operation, $operation1], $logger, $partnerLoader, $authorizationChecker);
    }

    function it_handles_operation(WubookOperationInterface $operation, WubookOperationInterface $operation1, AuthorizationCheckerInterface $authorizationChecker, Partner $partner, PartnerLoader $partnerLoader)
    {
        $json = [
            "hotel_auth" => [
                "hotel_id" => "00289058",
            ],
            "action" => "get_rates",
        ];
        $jsonString = json_encode($json);

        $partnerLoader->find('00289058')->willReturn($partner);
        $authorizationChecker->isGranted(WubookVoter::WUBOOK_OPERATION, $partner)->willReturn(true);
        $operation->supports("get_rates")->shouldBeCalled()->willReturn(false);
        $operation1->supports("get_rates")->shouldBeCalled()->willReturn(true);

        $operation1->handle(json_decode($jsonString), $partner)->shouldNotBeCalled();
        $operation1->handle(json_decode($jsonString), $partner)->shouldBeCalled()->willReturn($json);
        $this->handle(json_decode($jsonString))->shouldBe($json);
    }

    function it_throws_exception(
        WubookOperationInterface $operation,
        WubookOperationInterface $operation1,
        CmhubLogger $logger,
        AuthorizationCheckerInterface $authorizationChecker,
        Partner $partner,
        PartnerLoader $partnerLoader
    )
    {
        $json = [
            "hotel_auth" => [
                "hotel_id" => "00145577",
            ],
            "action" => "get_rates",
        ];
        $jsonString = json_encode($json);

        $partnerLoader->find('00145577')->willReturn($partner);
        $authorizationChecker->isGranted(WubookVoter::WUBOOK_OPERATION, $partner)->willReturn(true);
        $operation->supports("get_rates")->shouldBeCalled()->willReturn(false);
        $operation1->supports("get_rates")->shouldBeCalled()->willReturn(false);

        $operation1->handle(json_decode($jsonString))->shouldNotBeCalled();
        $operation1->handle(json_decode($jsonString))->shouldNotBeCalled();
        $logger->addRecord(\Monolog\Logger::ALERT, "Wubook Operation get_rates not found", Argument::type('array'), $this)->shouldBeCalled();
        $this->shouldThrow(WubookOperationNotFoundException::class)->during('handle', [json_decode($jsonString)]);
    }

    function it_throws_access_denied(AuthorizationCheckerInterface $authorizationChecker, Partner $partner, PartnerLoader $partnerLoader)
    {
        $json = [
            "hotel_auth" => [
                "hotel_id" => "00145577",
            ],
            "action" => "get_rates",
        ];
        $jsonString = json_encode($json);

        $partnerLoader->find('00145577')->willReturn($partner);
        $authorizationChecker->isGranted(WubookVoter::WUBOOK_OPERATION, $partner)->willReturn(false);

        $this->shouldThrow(AccessDeniedException::class)->during('handle', [json_decode($jsonString)]);
    }

    function it_throws_partner_not_found_exception(PartnerLoader $partnerLoader)
    {
        $json = [
            "hotel_auth" => [
                "hotel_id" => "00145577",
            ],
            "action" => "get_rates",
        ];

        $jsonString = json_encode($json);
        $partnerLoader->find('00145577')->willReturn(null);
        $this->shouldThrow(PartnerNotFoundException::class)->during('handle', [json_decode($jsonString)]);
    }
}
