<?php

namespace spec\App\Service\ChannelManager\AvailPro;

use App\Entity\Booking;
use App\Entity\ChannelManager;
use App\Entity\Partner;
use App\Exception\ValidationException;
use App\Model\BookingCollection;
use App\Model\BookingInterface;
use App\Model\PartnerInterface;
use App\Model\ProductCollection;
use App\Model\Rate;
use App\Model\RatePlanCode;
use App\Service\BookingEngineInterface;
use App\Service\ChannelManager\AvailPro\AvailProIntegration;
use App\Service\ChannelManager\AvailPro\Serializer\AvailProSerializer;
use App\Service\ChannelManager\ChannelManagerList;
use App\Service\HubEngine\CmHubBookingEngine;
use App\Service\Loader\PartnerLoader;
use App\Service\Loader\ProductLoader;
use App\Utils\Monolog\CmhubLogger;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use PhpSpec\ObjectBehavior;
use PhpSpec\Wrapper\Collaborator;
use Prophecy\Argument;
use Twig\Environment;

class AvailProIntegrationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AvailProIntegration::class);
    }

    function let(
        Environment $templating,
        ProductLoader $productLoader,
        PartnerLoader $partnerLoader,
        AvailProSerializer $availProSerializer,
        BookingEngineInterface $bookingEngine,
        PartnerInterface $partner,
        CmhubLogger $logger,
        EntityManagerInterface $entityManager
    )
    {
        $this->beConstructedWith($templating, $productLoader, $partnerLoader, $availProSerializer, $bookingEngine, $logger, $entityManager);

        $partnerLoader->find('partner_id')->willReturn($partner);
    }

    function it_throws_exception_if_partner_not_found_get_hotel_action(Environment $templating, PartnerLoader $partnerLoader)
    {
        $templating
            ->render(
                AvailProIntegration::FAILURE_TEMPLATE,
                Argument::type('array')
            )
            ->willReturn('failure_template');

        $this->getHotel(null)->shouldBe('failure_template');

        $partnerLoader->find('partner_id')->willReturn();
        $this->getHotel('partner_id')->shouldBe('failure_template');
    }

    function it_renders_get_hotel_template_and_inject_product_data(Environment $templating, ProductLoader $productLoader, ProductCollection $products, PartnerInterface $partner, ChannelManager $channelManager, CmhubLogger $logger)
    {
        $partner->getIdentifier()->willReturn('abc123');
        $partner->getName()->willReturn('AvailPro');
        $partner->getChannelManager()->willReturn($channelManager);
        $channelManager->getIdentifier()->willReturn('abc321');
        $productLoader->getByPartner($partner)->willReturn($products);
        $templating
            ->render(
                AvailProIntegration::GET_HOTEL_TEMPLATE,
                [
                    'ratePlan'       => RatePlanCode::SBX,
                    'ratePlanName'   => Rate::SBX_RATE_PLAN_NAME,
                    'ratePlanRegime' => Rate::SBC_PLAN_REGIME,
                    'partner'        => $partner,
                    'products'       => $products,
                ]
            )
            ->shouldBeCalled()
            ->willReturn('the_view_data');

        $this->getHotel('partner_id')->shouldBe('the_view_data');
        $logger->addRecord(\Monolog\Logger::INFO, 'get_hotel', Argument::type('array'), $this);
    }

    /**
     * @param Collaborator|Environment    $templating
     * @param PartnerLoader|Collaborator      $partnerLoader
     * @param BookingCollection|Collaborator  $collection
     * @param BookingInterface|Collaborator   $booking
     * @param PartnerInterface|Collaborator   $partner
     * @param CmHubBookingEngine|Collaborator $bookingEngine
     *
     * @throws Exception
     */
    function it_get_bookings_with_partner(
        Environment $templating,
        PartnerLoader $partnerLoader,
        BookingCollection $collection,
        BookingInterface $booking,
        PartnerInterface $partner,
        EntityManagerInterface $entityManager,
        EntityRepository $entityRepository,
        CmHubBookingEngine $bookingEngine,
        ChannelManager $channelManager
    )
    {
        $hotelId = '00127978';

        $startDate = new DateTime('2017-10-03T20:00:00');
        $endDate = new DateTime('2019-10-06T21:00:00');
        $entityManager
            ->getRepository(ChannelManager::class)
            ->shouldBeCalled()
            ->willReturn($entityRepository);

        $entityRepository
            ->findOneBy(['identifier' => ChannelManagerList::AVAILPRO])
            ->shouldBeCalled()
            ->willReturn($channelManager);

        $partnerLoader->find($hotelId)->willReturn($partner);
        $collection->getBookings()->willReturn([$booking]);
        $bookingEngine->getBookings($startDate, $endDate, $channelManager, [$partner])->willReturn($collection);

        $templating->render(
            AvailProIntegration::GET_BOOKINGS_TEMPLATE,
            [
                'partner'  => $partner,
                'bookings' => $collection,
                'ratePlan' => 'SBX',
            ]
        )->shouldBeCalled()->willReturn('Booking Info');

        $this->getBookings($startDate, $endDate, $hotelId)->shouldBe('Booking Info');
    }

    /**
     * @param Collaborator|Environment    $templating
     * @param PartnerLoader|Collaborator      $partnerLoader
     * @param BookingCollection               $collection
     * @param Booking                         $booking
     * @param Partner                         $partner
     * @param EntityManagerInterface          $entityManager
     * @param EntityRepository                $entityRepository
     * @param CmHubBookingEngine|Collaborator $bookingEngine
     * @param ChannelManager                  $channelManager
     *
     * @throws Exception
     */
    function it_get_bookings_without_partner(
        Environment $templating,
        PartnerLoader $partnerLoader,
        BookingCollection $collection,
        Booking $booking,
        Partner $partner,
        EntityManagerInterface $entityManager,
        EntityRepository $entityRepository,
        CmHubBookingEngine $bookingEngine,
        ChannelManager $channelManager
    )
    {
        $hotelId = '';

        $startDate = new DateTime('2017-10-03T20:00:00');
        $endDate = new DateTime('2019-10-06T21:00:00');

        $entityManager
            ->getRepository(ChannelManager::class)
            ->shouldBeCalled()
            ->willReturn($entityRepository);

        $entityRepository
            ->findOneBy(['identifier' => ChannelManagerList::AVAILPRO])
            ->shouldBeCalled()
            ->willReturn($channelManager);

        $booking->getPartner()->willReturn($partner);
        $partner->getIdentifier()->willReturn('partner');

        $partnerLoader->find($hotelId)->willReturn(null);
        $collection->getBookings()->willReturn([$booking]);

        $bookingEngine
            ->getBookings(
                Argument::that(function (\DateTime $start) {
                    return $start->format('Y-m-d') === '2017-10-03';
                }),
                Argument::that(function (\DateTime $end) {
                    return $end->format('Y-m-d') === '2019-10-06';
                }),
                $channelManager
            )
            ->willReturn($collection);

        $templating->render(
            AvailProIntegration::GET_BOOKINGS_ALL_PARTNERS_TEMPLATE,
            [
                'bookingsAllPartner' => ['partner' => [$booking]],
                'ratePlan'           => 'SBX',
            ]
        )->willReturn('Booking Info');

        $this->getBookings($startDate, $endDate, $hotelId)->shouldBe('Booking Info');
    }

    function it_throw_exception_on_date_validation(
        Environment $templating
    )
    {
        $hotelId = '';

        $startDate = new DateTime('2019-12-10');
        $endDate = new DateTime('2019-10-06');

        $templating
            ->render(
                AvailProIntegration::FAILURE_TEMPLATE,
                Argument::type('array')
            )
            ->willReturn('failure_template');

        $this->getBookings($startDate, $endDate, $hotelId)->shouldBe('failure_template');
    }
}
