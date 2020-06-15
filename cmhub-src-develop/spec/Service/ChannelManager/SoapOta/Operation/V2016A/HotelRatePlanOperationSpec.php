<?php

namespace spec\App\Service\ChannelManager\SoapOta\Operation\V2016A;

use App\Entity\ChannelManager;
use App\Exception\AccessDeniedException;
use App\Model\PartnerInterface;
use App\Model\ProductCollection;
use App\Model\ProductInterface;
use App\Model\Rate;
use App\Model\RatePlanCode;
use App\Security\Voter\PartnerVoter;
use App\Service\ChannelManager\SoapOta\Operation\V2016A\HotelRatePlanOperation;
use App\Service\Loader\PartnerLoader;
use App\Service\Loader\ProductLoader;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class HotelRatePlanOperationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(HotelRatePlanOperation::class);
    }

    function let(
        PartnerLoader $partnerLoader,
        AuthorizationCheckerInterface $authorizationChecker,
        CmhubLogger $logger,
        ProductLoader $productLoader,
        PartnerInterface $partner,
        ChannelManager $channelManager
    )
    {
        $this->beConstructedWith($partnerLoader, $authorizationChecker, $logger, $productLoader);

        $partnerLoader->find('partner_id')->willReturn($partner);
        $partner->getChannelManager()->willReturn($channelManager);
        $authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)->willReturn(true);
        $channelManager->getIdentifier()->willReturn('cm_identifier');
        $partner->getIdentifier()->willReturn('partner_id');
        $partner->getName()->willReturn('partner_name');
    }

    function it_throws_access_denied_exception_if_partner_is_not_granted(PartnerInterface $partner, AuthorizationCheckerInterface $authorizationChecker)
    {
        $authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)->willReturn(false);
        $this->shouldThrow(AccessDeniedException::class)->during('handle', [json_decode(json_encode($this->testData))]);
    }

    function it_handles_operation(ProductInterface $product, ProductLoader $productLoader, PartnerInterface $partner, ProductCollection $productCollection, CmhubLogger $logger)
    {
        $productLoader
            ->getByPartner($partner)
            ->willReturn($productCollection);

        $productCollection->toArray()->willReturn([$product]);
        $product->getIdentifier()->willReturn('identifier');

        $logger->addOperationInfo(LogAction::GET_RATE_PLANS, $partner, $this)->shouldBeCalled();

        $this
            ->handle(json_decode(json_encode($this->testData)))
            ->shouldBe(
                [
                    'RatePlans' => [
                        'RatePlan' => [
                            [
                                'RatePlanCode'     => RatePlanCode::SBX,
                                'Description'      => [
                                    'Name' => 'Name',
                                    'Text' => Rate::SBX_RATE_PLAN_NAME,
                                ],
                                'SellableProducts' => [
                                    'SellableProduct' => [
                                        ['InvTypeCode' => 'identifier']
                                    ]
                                ],
                            ],
                        ],
                    ],
                ]
            );
    }

    protected $testData = [
        'RatePlans' => [
            'RatePlan' => [
                'HotelRef' => [
                    'HotelCode' => 'partner_id'
                ]
            ]
        ]
    ];
}