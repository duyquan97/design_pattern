<?php

namespace spec\App\Service\ChannelManager\SoapOta\Operation\V2016A;

use App\Exception\AccessDeniedException;
use App\Exception\PartnerNotFoundException;
use App\Model\PartnerInterface;
use App\Model\ProductCollection;
use App\Security\Voter\PartnerVoter;
use App\Service\ChannelManager\SoapOta\Operation\V2016A\HotelDescriptiveInfoOperation;
use App\Service\ChannelManager\SoapOta\Serializer\SoapSerializer;
use App\Service\Loader\PartnerLoader;
use App\Service\Loader\ProductLoader;
use App\Utils\Monolog\CmhubLogger;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class HotelDescriptiveInfoOperationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(HotelDescriptiveInfoOperation::class);
    }

    function let(SoapSerializer $soapSerializer, PartnerLoader $partnerLoader, ProductLoader $productLoader, AuthorizationCheckerInterface $authorizationChecker, CmhubLogger $logger)
    {
        $this->beConstructedWith($soapSerializer, $partnerLoader, $productLoader, $authorizationChecker, $logger);
    }

    function it_supports_operation()
    {
        $this->supports(HotelDescriptiveInfoOperation::OPERATION_NAME)->shouldBe(true);
    }

    function it_handles_operation(SoapSerializer $soapSerializer, PartnerLoader $partnerLoader, ProductLoader $productLoader, PartnerInterface $partner, ProductCollection $productCollection, AuthorizationCheckerInterface $authorizationChecker)
    {
        $request = [
            "HotelDescriptiveInfos" => [
                "HotelDescriptiveInfo" => [
                    "HotelCode" => "1",
                    "FacilityInfo" => [
                        "SendGuestRooms" => "True",
                    ],
                ],
            ],
        ];

        $partnerLoader->find('1')->willReturn($partner);
        $authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)->willReturn(true);
        $productLoader->getByPartner($partner)->willReturn($productCollection);

        $soapSerializer->normalize($productCollection)->willReturn($product = [ "package" => "package1"]);

        $response = [
            "HotelDescriptiveContents" => [
                "HotelDescriptiveContent" => [
                    "FacilityInfo" => $product,
                ],
            ],
        ];

        $this->handle(json_decode(json_encode($request)))->shouldBeLike($response);
    }

    function it_handles_operation_but_return_empty(SoapSerializer $soapSerializer, PartnerLoader $partnerLoader, ProductLoader $productLoader, PartnerInterface $partner, ProductCollection $productCollection, AuthorizationCheckerInterface $authorizationChecker)
    {
        $request = [
            "HotelDescriptiveInfos" => [
                "HotelDescriptiveInfo" => [
                    "HotelCode" => "1",
                ],
            ],
        ];

        $partnerLoader->find('1')->willReturn($partner);
        $authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)->willReturn(true);
        $productLoader->getByPartner($partner)->willReturn($productCollection);

        $soapSerializer->normalize($productCollection)->willReturn($product = ["package" => "package1"]);

        $this->handle(json_decode(json_encode($request)))->shouldBeLike([]);
    }

    function it_gets_access_denied(PartnerLoader $partnerLoader, PartnerInterface $partner, AuthorizationCheckerInterface $authorizationChecker)
    {
        $request = [
            "HotelDescriptiveInfos" => [
                "HotelDescriptiveInfo" => [
                    "HotelCode" => "1",
                ],
            ],
        ];

        $partnerLoader->find('1')->willReturn($partner);
        $authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)->willReturn(false);

        $this->shouldThrow(AccessDeniedException::class)->during('handle', [json_decode(json_encode($request))]);
    }
}
