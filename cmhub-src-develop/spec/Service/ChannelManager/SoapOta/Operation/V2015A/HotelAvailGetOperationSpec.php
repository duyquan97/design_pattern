<?php

namespace spec\App\Service\ChannelManager\SoapOta\Operation\V2015A;

use App\Entity\Partner;
use App\Entity\Product;
use App\Exception\AccessDeniedException;
use App\Exception\DateFormatException;
use App\Exception\ValidationException;
use App\Model\Factory\ProductCollectionFactory;
use App\Model\PartnerInterface;
use App\Model\ProductAvailabilityCollection;
use App\Model\ProductCollection;
use App\Model\RatePlanCode;
use App\Security\Voter\PartnerVoter;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\SoapOta\Operation\V2015A\HotelAvailGetOperation;
use App\Service\ChannelManager\SoapOta\Serializer\SoapSerializer;
use App\Service\Loader\PartnerLoader;
use App\Service\Loader\ProductLoader;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class HotelAvailGetOperationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(HotelAvailGetOperation::class);
    }

    function let(
        BookingEngineInterface $bookingEngine,
        PartnerLoader $partnerLoader,
        ProductLoader $productLoader,
        SoapSerializer $soapSerializer,
        AuthorizationCheckerInterface $authorizationChecker,
        ProductCollectionFactory $productCollectionFactory,
        CmhubLogger $logger,
        PartnerInterface $partner
    )
    {
        $this->beConstructedWith($bookingEngine, $partnerLoader, $productLoader, $soapSerializer, $authorizationChecker, $productCollectionFactory, $logger);

        $partnerLoader->find('partner_id')->willReturn($partner);
        $authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)->willReturn(true);
    }

    function it_support_operation()
    {
        $this->supports(HotelAvailGetOperation::OPERATION_NAME)->shouldBe(true);
    }

    function it_deny_access(PartnerLoader $partnerLoader, Partner $partner, AuthorizationCheckerInterface $authorizationChecker)
    {
        $data = json_decode(json_encode($this->testData));
        $partnerLoader->find('partner_id')->willReturn($partner);
        $authorizationChecker->isGranted(PartnerVoter::OTA_OPERATION, $partner)->willReturn(false);
        $this->shouldThrow(AccessDeniedException::class)->during('handle', [$data]);
    }

    function it_throw_datetime_exception(ProductLoader $productLoader, Partner $partner, ProductCollectionFactory $productCollectionFactory,
                                         ProductCollection $collection, Product $product)
    {
        $data = json_decode(json_encode($this->invalidDateData));
        $productCollectionFactory->create($partner)->willReturn($collection);
        $productLoader->find($partner, 'product_id')->willReturn($product);
        $collection->addProduct($product)->shouldBeCalled();
        $collection->isEmpty()->willReturn(false);

        $this->shouldThrow(DateFormatException::class)->during('handle', [$data]);
    }

    function it_throw_date_validation_exception(ProductLoader $productLoader, Partner $partner, ProductCollectionFactory $productCollectionFactory,
                                         ProductCollection $collection, Product $product)
    {
        $data = json_decode(json_encode($this->invalidStartDateData));
        $productCollectionFactory->create($partner)->willReturn($collection);
        $productLoader->find($partner, 'product_id')->willReturn($product);
        $collection->addProduct($product)->shouldBeCalled();
        $collection->isEmpty()->willReturn(false);

        $this->shouldThrow(ValidationException::class)->during('handle', [$data]);
    }


    function it_handle_operation_with_candidate(ProductLoader $productLoader, Partner $partner, AuthorizationCheckerInterface $authorizationChecker,
        ProductCollectionFactory $productCollectionFactory, ProductCollection $collection, Product $product, CmhubLogger $logger,
        BookingEngineInterface $bookingEngine, ProductAvailabilityCollection $availCollection, SoapSerializer $soapSerializer)
    {
        $expected = [
            'AvailStatusMessages' => [
                'HotelCode' => 'partner_id',
                'AvailStatusMessage' => [
                    'StatusApplicationControl' => [
                        'Start' => '2019-10-01',
                        'End' => '2019-10-01',
                        'InvTypeCode' => 'product_id',
                        'RatePlanCode' => RatePlanCode::SBX,
                    ],
                    'RestrictionStatus' => [
                        'Restriction' => 'Master',
                        'Status' => 'Close',
                    ],
                ],
            ],
        ];

        $productCollectionFactory->create($partner)->willReturn($collection);
        $productLoader->find($partner, 'product_id')->willReturn($product);
        $collection->addProduct($product)->shouldBeCalled();
        $collection->isEmpty()->willReturn(false);
        $collection->toArray()->willReturn([$product]);
        $logger->addOperationInfo(LogAction::GET_PRODUCTS, $partner, $this)->shouldBeCalled();

        $bookingEngine->getAvailabilities(
            $partner,
            Argument::that(function(\DateTime $date) {
                return '2019-10-01' === $date->format('Y-m-d');
            }),
            Argument::that(function(\DateTime $date) {
                return '2019-10-01' === $date->format('Y-m-d');
            }),
            [$product]
        )->willReturn($availCollection);

        $soapSerializer->normalize(
            $collection,
            Argument::type('array')
        )->shouldBeCalled()->willReturn($expected);

        $this->handle(json_decode(json_encode($this->testData)));
    }

    protected $testData = [
        'HotelAvailRequests' => [
            'HotelAvailRequest' => [
                'HotelRef' => [
                    'HotelCode' => 'partner_id'
                ],
                'DateRange' => [
                    'Start' => '2019-10-01',
                    'End' => '2019-10-01'
                ],
                'RatePlanCandidates' => [
                    'RatePlanCandidate' => [
                        'RatePlanCode' => 'SBX'
                    ]
                ],
                'RoomTypeCandidates' => [
                    'RoomTypeCandidate' => [
                        'RoomTypeCode' => 'product_id'
                    ]
                ]
            ]
        ]
    ];

    protected $invalidDateData = [
        'HotelAvailRequests' => [
            'HotelAvailRequest' => [
                'HotelRef' => [
                    'HotelCode' => 'partner_id'
                ],
                'DateRange' => [
                    'Start' => '01-10-2019',
                    'End' => '2019-10-01'
                ],
                'RatePlanCandidates' => [
                    'RatePlanCandidate' => [
                        'RatePlanCode' => 'SBX'
                    ]
                ],
                'RoomTypeCandidates' => [
                    'RoomTypeCandidate' => [
                        'RoomTypeCode' => 'product_id'
                    ]
                ]
            ]
        ]
    ];

    protected $invalidStartDateData = [
        'HotelAvailRequests' => [
            'HotelAvailRequest' => [
                'HotelRef' => [
                    'HotelCode' => 'partner_id'
                ],
                'DateRange' => [
                    'Start' => '2019-10-04',
                    'End' => '2019-10-01'
                ],
                'RatePlanCandidates' => [
                    'RatePlanCandidate' => [
                        'RatePlanCode' => 'SBX'
                    ]
                ],
                'RoomTypeCandidates' => [
                    'RoomTypeCandidate' => [
                        'RoomTypeCode' => 'product_id'
                    ]
                ]
            ]
        ]
    ];
}
