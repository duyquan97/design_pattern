<?php

namespace App\Booking;

use App\Booking\Model\Booking;
use App\Booking\Model\BookingStatus;
use App\Booking\Model\ExperienceComponent;
use App\Entity\Booking as BookingEntity;
use App\Entity\BookingProduct;
use App\Entity\BookingProductRate;
use App\Entity\Factory\BookingFactory;
use App\Entity\Guest;
use App\Entity\Partner;
use App\Entity\Product;
use App\Exception\BookingAlreadyProcessedException;
use App\Repository\BookingRepository;
use App\Repository\ExperienceRepository;
use App\Repository\PartnerRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class BookingManager
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingManager
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var BookingRepository
     */
    private $bookingRepository;

    /**
     * @var PartnerRepository
     */
    private $partnerRepository;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var ExperienceRepository
     */
    private $experienceRepository;

    /**
     * @var BookingProcessorManager
     */
    private $bookingProcessorManager;

    /**
     * @var BookingFactory
     */
    private $bookingFactory;

    /**
     * BookingManager constructor.
     *
     * @param EntityManagerInterface  $entityManager
     * @param BookingRepository       $bookingRepository
     * @param PartnerRepository       $partnerRepository
     * @param ProductRepository       $productRepository
     * @param ExperienceRepository    $experienceRepository
     * @param BookingProcessorManager $bookingProcessorManager
     * @param BookingFactory          $bookingFactory
     */
    public function __construct(EntityManagerInterface $entityManager, BookingRepository $bookingRepository, PartnerRepository $partnerRepository, ProductRepository $productRepository, ExperienceRepository $experienceRepository, BookingProcessorManager $bookingProcessorManager, BookingFactory $bookingFactory)
    {
        $this->entityManager = $entityManager;
        $this->bookingRepository = $bookingRepository;
        $this->partnerRepository = $partnerRepository;
        $this->productRepository = $productRepository;
        $this->experienceRepository = $experienceRepository;
        $this->bookingProcessorManager = $bookingProcessorManager;
        $this->bookingFactory = $bookingFactory;
    }

    /**
     *
     * @param Booking $processBooking
     *
     * @return BookingEntity
     *
     * @throws BookingAlreadyProcessedException
     */
    public function create(Booking $processBooking)
    {
        if ($booking = $this->bookingRepository->findOneBy(['identifier' => $processBooking->getIdentifier()])) {
            throw new BookingAlreadyProcessedException($booking);
        }

        if (!$partner = $this->partnerRepository->findOneBy(['identifier' => $processBooking->getPartner()])) {
            $partner = new Partner();
            $partner->setIdentifier($processBooking->getPartner());
            $this->entityManager->persist($partner);
            $this->entityManager->flush();
        }

        $booking = $this->bookingFactory->create();
        $booking
            ->setIdentifier($processBooking->getIdentifier())
            ->setCreatedAt($processBooking->getCreatedAt())
            ->setCurrency($processBooking->getCurrency())
            ->setStatus($processBooking->isConfirmed() ? BookingStatus::CONFIRMED : BookingStatus::CANCELLED)
            ->setStartDate($processBooking->getStartDate())
            ->setEndDate($processBooking->getEndDate())
            ->setTotalAmount($processBooking->getPrice())
            ->setVoucherNumber($processBooking->getVoucherNumber())
            ->setPartner($partner);

        if ($processBooking->getExperience()) {
            $booking->setComponents(
                array_map(
                    function (ExperienceComponent $component) {
                        return $component->getName();
                    },
                    $processBooking->getExperience()->getComponents()
                )
            );
        }

        $experience = $this->experienceRepository->findOneBy(['identifier' => $processBooking->getExperience()->getId()]);
        if ($experience) {
            $booking->setExperience($experience);
        }

        foreach ($processBooking->getRoomTypes() as $room) {
            if (!$product = $this->productRepository->findOneBy(['identifier' => $room->getId()])) {
                $product = (new Product())
                    ->setIdentifier($room->getId())
                    ->setName($room->getName() ?? '')
                    ->setPartner($partner);
                $this->entityManager->persist($product);
                $this->entityManager->flush();
            }

            $bookingProduct = (new BookingProduct())
                ->setTotalAmount($processBooking->getPrice())
                ->setCurrency($processBooking->getCurrency())
                ->setProduct($product)
                ->setBooking($booking);

            foreach ($room->getGuests() as $bookingGuest) {
                $guest = (new Guest())
                    ->setName($bookingGuest->getName())
                    ->setSurname($bookingGuest->getSurname())
                    ->setAge($bookingGuest->getAge())
                    ->setEmail($bookingGuest->getEmail())
                    ->setIsMain($bookingGuest->isMain())
                    ->setPhone($bookingGuest->getPhone())
                    ->setBookingProduct($bookingProduct)
                    ->setCountryCode($bookingGuest->getCountryCode());

                $bookingProduct->addGuest($guest);
            }

            foreach ($room->getDailyRates() as $dailyRate) {
                /** @var BookingProductRate $bookingProductRate */
                $bookingProductRate = new BookingProductRate();
                $bookingProductRate
                    ->setDate($dailyRate->getDate())
                    ->setBookingProduct($bookingProduct)
                    ->setAmount($dailyRate->getPrice())
                    ->setCurrency($processBooking->getCurrency());

                $bookingProduct->addRate($bookingProductRate);
            }

            $booking->addBookingProduct($bookingProduct);
        }

        /* @var BookingEntity $booking */
        $booking = $this->bookingProcessorManager->process($booking);
        $this->entityManager->persist($booking->setProcessed(true));
        $this->entityManager->flush();

        return $booking;
    }


    /**
     * @param string $identifier
     *
     * @return object|null
     *
     * @throws NotFoundHttpException
     */
    public function cancel(string $identifier)
    {
        if (!$booking = $this->bookingRepository->findOneBy(['identifier' => $identifier])) {
            throw new NotFoundHttpException('The requested resource does not exist or you don\'t have enough permission');
        }

        $booking->setStatus(BookingStatus::CANCELLED);

        $this->entityManager->persist($booking);
        $this->entityManager->flush();

        return $booking;
    }

    /**
     * @param string $identifier
     *
     * @return BookingEntity
     */
    public function get(string $identifier)
    {
        return $this->bookingRepository->findOneBy(['identifier' => $identifier]);
    }
}
