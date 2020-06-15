<?php

namespace spec\App\Service\ChannelManager\SoapOta\Operation\V2007B;

use App\Entity\ChannelManager;
use App\Exception\AccessDeniedException;
use App\Model\PartnerInterface;
use App\Model\ProductCollection;
use App\Security\Voter\PartnerVoter;
use App\Service\ChannelManager\SoapOta\Operation\V2007B\HotelAvailOperation;
use App\Service\ChannelManager\SoapOta\Serializer\SoapSerializer;
use App\Service\Loader\PartnerLoader;
use App\Service\Loader\ProductLoader;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class HotelAvailOperationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(HotelAvailOperation::class);
    }

    function let(
        CmhubLogger $logger,
        PartnerLoader $partnerLoader,
        ProductLoader $productLoader,
        SoapSerializer $soapSerializer,
        AuthorizationCheckerInterface $authorizationChecker,
        PartnerInterface $partner,
        ChannelManager $channelManager
    ) {
        $this->beConstructedWith($partnerLoader, $productLoader, $soapSerializer, $authorizationChecker, $logger);

        $partnerLoader->find('PARTNERID')->willReturn($partner);
        $partner->getChannelManager()->willReturn($channelManager);
        $channelManager->getIdentifier()->willReturn('cm_identifier');
        $partner->getIdentifier()->willReturn('partner_id');
        $partner->getName()->willReturn('partner_name');
        $authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)->willReturn(true);
    }

    function it_throws_access_denied_exception_if_partner_not_granted(PartnerInterface $partner, AuthorizationCheckerInterface $authorizationChecker)
    {
        $authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)->shouldBeCalled()->willReturn(false);
        $this->shouldThrow(AccessDeniedException::class)->during('handle', [json_decode(json_encode($this->testData))]);
    }

    function it_handles_operation(ProductLoader $productLoader, PartnerInterface $partner, ProductCollection $collection, SoapSerializer $soapSerializer, CmhubLogger $logger)
    {
        $productLoader->getByPartner($partner)->willReturn($collection);
        $soapSerializer->normalize($collection)->shouldBeCalled()->willReturn(['an' => 'array']);
        $logger->addOperationInfo(LogAction::GET_PRODUCTS, $partner, $this)->shouldBeCalled();
        $this->handle(json_decode(json_encode($this->testData)))->shouldBe(['an' => 'array']);
    }

    protected $testData = [
        'AvailRequestSegments' => [
            'AvailRequestSegment' => [
                'HotelSearchCriteria' => [
                    'Criterion' => [
                        'HotelRef' => [
                            'HotelCode' => 'PARTNERID'
                        ]
                    ]
                ]
            ]
        ]
    ];
}
