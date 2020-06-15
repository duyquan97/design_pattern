<?php

namespace spec\App\Booking;

use App\Booking\BookingManager;
use App\Booking\BookingProcessorManager;
use App\Booking\Model\Booking;
use App\Booking\Model\BookingStatus;
use App\Booking\Model\Experience;
use App\Booking\Model\ExperienceComponent;
use App\Booking\Model\Guest;
use App\Booking\Model\Rate;
use App\Booking\Model\Room;
use App\Entity\Booking as BookingEntity;
use App\Entity\BookingProduct;
use App\Entity\Experience as ExperienceEntity;
use App\Entity\Factory\BookingFactory;
use App\Entity\Partner;
use App\Exception\BookingAlreadyProcessedException;
use App\Repository\BookingRepository;
use App\Repository\ExperienceRepository;
use App\Repository\PartnerRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BookingManagerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BookingManager::class);
    }

    function let(EntityManagerInterface $entityManager, BookingRepository $bookingRepository, PartnerRepository $partnerRepository, ProductRepository $productRepository, ExperienceRepository $experienceRepository, BookingProcessorManager $bookingProcessorManager, BookingFactory $bookingFactory)
    {
        $this->beConstructedWith($entityManager, $bookingRepository, $partnerRepository, $productRepository, $experienceRepository, $bookingProcessorManager, $bookingFactory);
    }

    function it_cancel_not_found_booking(BookingRepository $bookingRepository)
    {
        $bookingRepository->findOneBy(['identifier' => 'id'])->willReturn(null);
        $this->shouldThrow(NotFoundHttpException::class)->during('cancel', ['id']);
    }

    function it_cancel_booking(BookingRepository $bookingRepository, BookingEntity $booking, EntityManagerInterface $entityManager)
    {
        $bookingRepository->findOneBy(['identifier' => 'id'])->willReturn($booking);

        $booking->setStatus(BookingStatus::CANCELLED)->shouldBeCalled()->willReturn($booking);

        $entityManager->persist($booking)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $this->cancel('id')->shouldReturn($booking);
    }

    function it_process_existed_booking(BookingRepository $bookingRepository, BookingEntity $booking, Booking $processBooking)
    {
        $processBooking->getIdentifier()->willReturn('booking_id');
        $bookingRepository->findOneBy(['identifier' => 'booking_id'])->willReturn($booking);
        $booking->getReservationId()->willReturn('booking_id');

        $this->shouldThrow(BookingAlreadyProcessedException::class)->during('create', [$processBooking]);
    }

    public function it_create_booking(Booking $processBooking, PartnerRepository $partnerRepository, Partner $partner, BookingRepository $bookingRepository,
      ExperienceRepository $experienceRepository, Experience $experience, BookingProcessorManager $bookingProcessorManager, BookingEntity $booking,
      ExperienceEntity $experienceEntity, Room $room1, Room $room2, ExperienceComponent $experienceComponent, Guest $guest1, Guest $guest2, Rate $rate1,
      Rate $rate2, BookingFactory $bookingFactory)
    {
        $createdAt = new \DateTime();
        $startDate = \DateTime::createFromFormat('!Y-m-d', '2020-05-15');
        $endDate = \DateTime::createFromFormat('!Y-m-d', '2020-05-15');

        $bookingFactory->create()->willReturn($booking);
        $booking->setIdentifier('booking_id')->willReturn($booking);
        $booking->setCreatedAt($createdAt)->willReturn($booking);
        $booking->setStartDate($startDate)->willReturn($booking);
        $booking->setEndDate($endDate)->willReturn($booking);
        $booking->setCurrency('EUR')->willReturn($booking);
        $booking->setStatus(BookingStatus::CONFIRMED)->willReturn($booking);
        $booking->setTotalAmount(100)->willReturn($booking);
        $booking->setVoucherNumber('voucher_number')->willReturn($booking);
        $booking->setPartner($partner)->willReturn($booking);
        $booking->setComponents(['component name'])->willReturn($booking);
        $booking->setExperience($experienceEntity)->willReturn($booking);
        $booking->addBookingProduct(Argument::type(BookingProduct::class))->shouldBeCalled()->willReturn($booking);
        $booking->setProcessed(true)->willReturn($booking);

        $bookingRepository->findOneBy(['identifier' => 'booking_id'])->willReturn(null);

        $processBooking->getPartner()->willReturn('partner_id');
        $partnerRepository->findOneBy(['identifier' => 'partner_id'])->willReturn($partner);

        $processBooking->getIdentifier()->willReturn('booking_id');
        $processBooking->getCurrency()->willReturn('EUR');
        $processBooking->getCreatedAt()->willReturn($createdAt);
        $processBooking->getStartDate()->willReturn($startDate);
        $processBooking->getEndDate()->willReturn($endDate);
        $processBooking->isConfirmed()->willReturn(true);
        $processBooking->getPrice()->willReturn(100);
        $processBooking->getVoucherNumber()->willReturn('voucher_number');
        $processBooking->getExperience()->willReturn($experience);
        $experience->getId()->willReturn('experience_id');
        $experience->getComponents()->willReturn([$experienceComponent]);
        $experienceComponent->getName()->willReturn('component name');
        $experienceRepository->findOneBy(['identifier' => 'experience_id'])->willReturn($experienceEntity);

        $processBooking->getRoomTypes()->willReturn([$room1, $room2]);

        $room1->getId()->willReturn('room_1');
        $room1->getName()->willReturn('room 1');
        $room1->getGuests()->willReturn([$guest1]);
        $room1->getDailyRates()->willReturn([$rate1]);
        $room2->getId()->willReturn('room_2');
        $room2->getName()->willReturn('room 2');
        $room2->getGuests()->willReturn([$guest2]);
        $room2->getDailyRates()->willReturn([$rate2]);

        $guest1->getName()->willReturn('guest 1');
        $guest1->getSurname()->willReturn('sur 1');
        $guest1->getAge()->willReturn(18);
        $guest1->getCountryCode()->willReturn('VN');
        $guest1->getEmail()->willReturn('guest1@example.com');
        $guest1->isMain()->willReturn(true);
        $guest1->getPhone()->willReturn('phone 1');
        $guest2->getName()->willReturn('guest 2');
        $guest2->getSurname()->willReturn('sur 2');
        $guest2->getAge()->willReturn(18);
        $guest2->getCountryCode()->willReturn('VN');
        $guest2->getEmail()->willReturn('guest2@example.com');
        $guest2->isMain()->willReturn(true);
        $guest2->getPhone()->willReturn('phone 2');

        $rate1->getDate()->willReturn($startDate);
        $rate1->getPrice()->willReturn(100);
        $rate2->getDate()->willReturn($startDate);
        $rate2->getPrice()->willReturn(100);

        $bookingProcessorManager->process($booking)->willReturn($booking);

        $this->create($processBooking)->shouldReturn($booking);
    }
}
