<?php

namespace spec\App\Service\Synchronizer\Diff;

use App\Entity\Partner;
use App\Exception\IresaClientException;
use App\Model\Availability;
use App\Model\AvailabilitySource;
use App\Model\Factory\ProductAvailabilityCollectionFactory;
use App\Model\Factory\ProductAvailabilityFactory;
use App\Model\PartnerInterface;
use App\Model\ProductAvailability;
use App\Model\ProductAvailabilityCollection;
use App\Model\ProductCollection;
use App\Model\ProductInterface;
use App\Service\BookingEngineManager;
use App\Service\HubEngine\CmHubBookingEngine;
use App\Service\Iresa\IresaBookingEngine;
use App\Service\Loader\PartnerLoader;
use App\Service\Loader\ProductLoader;
use App\Service\Synchronizer\Diff\AvailabilityDiff;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AvailabilityDiffSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AvailabilityDiff::class);
    }

    public function let(IresaBookingEngine $iresaBookingEngine, BookingEngineManager $bookingEngine, ProductAvailabilityFactory $productAvailabilityFactory, ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory, ProductLoader $productLoader, PartnerLoader $partnerLoader, EntityManagerInterface $entityManager)
    {
        $this->beConstructedWith($iresaBookingEngine, $bookingEngine, $productAvailabilityFactory, $productAvailabilityCollectionFactory, $productLoader, $partnerLoader, $entityManager);
    }

    public function it_throw_iresa_exception(
        IresaBookingEngine $iresaBookingEngine,
        BookingEngineManager $bookingEngine,
        ProductAvailabilityFactory $productAvailabilityFactory,
        ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory,
        ProductLoader $productLoader,
        ProductAvailabilityCollection $productAvailabilityCollection,
        PartnerInterface $partner,
        ProductCollection $productCollection,
        ProductInterface $product,
        ProductInterface $product1,
        ProductAvailabilityCollection $cmhubAvailabilityCollection
    )
    {
        $productAvailabilityCollectionFactory->create($partner)->willReturn($productAvailabilityCollection);
        $productAvailabilityCollection->setSource(AvailabilitySource::ALIGNMENT)->shouldBeCalled()->willReturn($productAvailabilityCollection);
        $productLoader->getByPartner($partner)->willReturn($productCollection);
        $productCollection->toArray()->willReturn([
            $product,
            $product1
        ]);

        $bookingEngine
            ->getAvailabilities(
                $partner,
                Argument::type(\DateTime::class),
                Argument::type(\DateTime::class),
                [
                    $product,
                    $product1
                ]
            )
            ->willReturn($cmhubAvailabilityCollection);

        $iresaBookingEngine
            ->getAvailabilities($partner, Argument::type(\DateTime::class), Argument::type(\DateTime::class), [
                $product,
                $product1
            ])
            ->willThrow(IresaClientException::class);


        $cmhubAvailabilityCollection->getProductAvailabilities()->shouldNotBeCalled();
        $productAvailabilityFactory->create($product)->shouldNotBeCalled();
        $productAvailabilityFactory->create($product1)->shouldNotBeCalled();

        $this->shouldThrow(IresaClientException::class)->during('diff', [$partner, date_create(), date_create('+1 day')]);
    }

    public function it_finds_discrepancies_first_sync_or_connected_at_null(
        IresaBookingEngine $iresaBookingEngine,
        BookingEngineManager $bookingEngine,
        ProductAvailabilityFactory $productAvailabilityFactory,
        ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory,
        ProductLoader $productLoader,
        ProductAvailabilityCollection $productAvailabilityCollection,
        PartnerInterface $partner,
        ProductCollection $productCollection,
        ProductInterface $product,
        ProductInterface $product1,
        ProductAvailabilityCollection $cmhubAvailabilityCollection,
        ProductAvailabilityCollection $iresaAvailabilityCollection,
        ProductAvailability $productAvailability,
        ProductAvailability $productAvailability1,
        ProductAvailability $availabilityDiff,
        Availability $availability,
        Availability $availability1,
        Availability $availability2,
        Availability $availability3,
        Availability $iresaAvailability,
        Availability $iresaAvailability1,
        Availability $iresaAvailability2,
        Availability $iresaAvailability3,
        PartnerLoader $partnerLoader,
        Partner $partnerEntity,
        EntityManagerInterface $entityManager
    )
    {
        $productAvailabilityCollectionFactory->create($partner)->willReturn($productAvailabilityCollection);
        $productAvailabilityCollection->setSource(AvailabilitySource::ALIGNMENT)->shouldBeCalled();
        $productLoader->getByPartner($partner)->willReturn($productCollection);
        $productCollection->toArray()->willReturn([
            $product,
            $product1
        ]);

        $bookingEngine
            ->getAvailabilities(
                $partner,
                Argument::type(\DateTime::class),
                Argument::type(\DateTime::class),
                [
                    $product,
                    $product1
                ]
            )
            ->willReturn($cmhubAvailabilityCollection);

        $iresaBookingEngine
            ->getAvailabilities($partner, Argument::type(\DateTime::class), Argument::type(\DateTime::class), [
                $product,
                $product1
            ])
            ->willReturn($iresaAvailabilityCollection);

        $iresaAvailabilityCollection->getAvailabilities()->willReturn([]);
        $partner->getConnectedAt()->willReturn(null);
        $partner->getIdentifier()->willReturn('12345');
        $partnerLoader->find('12345')->willReturn($partnerEntity);
//        $partnerEntity->setConnectedAt(Argument::type('datetime'))->shouldBeCalled()->willReturn($partnerEntity);
//        $entityManager->persist($partnerEntity)->shouldBeCalled();
//        $entityManager->flush()->shouldBeCalled();

        $cmhubAvailabilityCollection
            ->getProductAvailabilities()
            ->willReturn(
                [
                    $productAvailability,
                    $productAvailability1
                ]
            );

        $productAvailability->getProduct()->willReturn($product);
        $productAvailability1->getProduct()->willReturn($product1);

        $product->isMaster()->willReturn(true);
        $product1->isMaster()->willReturn(false);

        $productAvailabilityFactory->create($product)->shouldBeCalled()->willReturn($availabilityDiff);
        $productAvailabilityFactory->create($product1)->shouldNotBeCalled();

        $productAvailability->getAvailabilities()->willReturn([
            $availability,
            $availability1,
            $availability2,
            $availability3
        ]);
        $availability->getStart()->willReturn($date = date_create('2020-01-01'));
        $availability1->getStart()->willReturn($date1 = date_create('2020-01-02'));
        $availability2->getStart()->willReturn($date2 = date_create('2020-01-03'));
        $availability3->getStart()->willReturn($date3 = date_create('2020-01-04'));

        $iresaAvailabilityCollection->getByProductAndDate($product, $date)->willReturn($iresaAvailability);
        $iresaAvailabilityCollection->getByProductAndDate($product, $date1)->willReturn($iresaAvailability1);
        $iresaAvailabilityCollection->getByProductAndDate($product, $date2)->willReturn($iresaAvailability2);
        $iresaAvailabilityCollection->getByProductAndDate($product, $date3)->willReturn($iresaAvailability3);
        $iresaAvailability->getStock()->willReturn(1);
        $availability->getStock()->willReturn(2);
        $availability->isStopSale()->willReturn(false);

        $availabilityDiff->addAvailability($availability)->shouldBeCalled();

        $iresaAvailability1->getStock()->willReturn(0);
        $availability1->isStopSale()->willReturn(true);

        $availabilityDiff->addAvailability($availability1)->shouldNotBeCalled();

        $iresaAvailability2->getStock()->willReturn(22);
        $availability2->getStock()->willReturn(22);
        $availability2->isStopSale()->willReturn(false);

        $availabilityDiff->addAvailability($availability2)->shouldNotBeCalled();

        $iresaAvailability3->getStock()->willReturn(0);
        $availability3->getStock()->willReturn(3);
        $availability3->isStopSale()->willReturn(false);

        $availabilityDiff->addAvailability($availability3)->shouldBeCalled();

        $availabilityDiff->isEmpty()->willReturn(false);
        $productAvailabilityCollection->addProductAvailability($availabilityDiff)->shouldBeCalled();

        $this->diff($partner, date_create(), date_create('+1 day'))->shouldBe($productAvailabilityCollection);
    }

    public function it_finds_discrepancies(
        IresaBookingEngine $iresaBookingEngine,
        BookingEngineManager $bookingEngine,
        ProductAvailabilityFactory $productAvailabilityFactory,
        ProductAvailabilityCollectionFactory $productAvailabilityCollectionFactory,
        ProductLoader $productLoader,
        ProductAvailabilityCollection $productAvailabilityCollection,
        PartnerInterface $partner,
        ProductCollection $productCollection,
        ProductInterface $product,
        ProductInterface $product1,
        ProductAvailabilityCollection $cmhubAvailabilityCollection,
        ProductAvailabilityCollection $iresaAvailabilityCollection,
        ProductAvailability $productAvailability,
        ProductAvailability $productAvailability1,
        ProductAvailability $availabilityDiff,
        Availability $availability,
        Availability $availability1,
        Availability $availability2,
        Availability $availability3,
        Availability $iresaAvailability,
        Availability $iresaAvailability1,
        Availability $iresaAvailability2,
        Availability $iresaAvailability3
    )
    {
        $productAvailabilityCollectionFactory->create($partner)->willReturn($productAvailabilityCollection);
        $productAvailabilityCollection->setSource(AvailabilitySource::ALIGNMENT)->shouldBeCalled();
        $productLoader->getByPartner($partner)->willReturn($productCollection);
        $productCollection->toArray()->willReturn([
            $product,
            $product1
        ]);

        $bookingEngine
            ->getAvailabilities(
                $partner,
                Argument::type(\DateTime::class),
                Argument::type(\DateTime::class),
                [
                    $product,
                    $product1
                ]
            )
            ->willReturn($cmhubAvailabilityCollection);

        $iresaBookingEngine
            ->getAvailabilities($partner, Argument::type(\DateTime::class), Argument::type(\DateTime::class), [
                $product,
                $product1
            ])
            ->willReturn($iresaAvailabilityCollection);

        $iresaAvailabilityCollection->getAvailabilities()->willReturn([$productAvailability, $productAvailability1]);
        $partner->getConnectedAt()->willReturn(new \DateTime());

        $cmhubAvailabilityCollection
            ->getProductAvailabilities()
            ->willReturn(
                [
                    $productAvailability,
                    $productAvailability1
                ]
            );

        $productAvailability->getProduct()->willReturn($product);
        $productAvailability1->getProduct()->willReturn($product1);

        $product->isMaster()->willReturn(true);
        $product1->isMaster()->willReturn(false);

        $productAvailabilityFactory->create($product)->shouldBeCalled()->willReturn($availabilityDiff);
        $productAvailabilityFactory->create($product1)->shouldNotBeCalled();

        $productAvailability->getAvailabilities()->willReturn([
            $availability,
            $availability1,
            $availability2,
            $availability3
        ]);
        $availability->getStart()->willReturn($date = date_create('2020-01-01'));
        $availability1->getStart()->willReturn($date1 = date_create('2020-01-02'));
        $availability2->getStart()->willReturn($date2 = date_create('2020-01-03'));
        $availability3->getStart()->willReturn($date3 = date_create('2020-01-04'));

        $iresaAvailabilityCollection->getByProductAndDate($product, $date)->willReturn($iresaAvailability);
        $iresaAvailabilityCollection->getByProductAndDate($product, $date1)->willReturn($iresaAvailability1);
        $iresaAvailabilityCollection->getByProductAndDate($product, $date2)->willReturn($iresaAvailability2);
        $iresaAvailabilityCollection->getByProductAndDate($product, $date3)->willReturn($iresaAvailability3);
        $iresaAvailability->getStock()->willReturn(1);
        $availability->getStock()->willReturn(2);
        $availability->isStopSale()->willReturn(false);

        $availabilityDiff->addAvailability($availability)->shouldBeCalled();

        $iresaAvailability1->getStock()->willReturn(0);
        $availability1->isStopSale()->willReturn(true);

        $availabilityDiff->addAvailability($availability1)->shouldNotBeCalled();

        $iresaAvailability2->getStock()->willReturn(22);
        $availability2->getStock()->willReturn(22);
        $availability2->isStopSale()->willReturn(false);

        $availabilityDiff->addAvailability($availability2)->shouldNotBeCalled();

        $iresaAvailability3->getStock()->willReturn(0);
        $availability3->getStock()->willReturn(3);
        $availability3->isStopSale()->willReturn(false);

        $availabilityDiff->addAvailability($availability3)->shouldBeCalled();

        $availabilityDiff->isEmpty()->willReturn(false);
        $productAvailabilityCollection->addProductAvailability($availabilityDiff)->shouldBeCalled();

        $this->diff($partner, date_create(), date_create('+1 day'))->shouldBe($productAvailabilityCollection);
    }
}
