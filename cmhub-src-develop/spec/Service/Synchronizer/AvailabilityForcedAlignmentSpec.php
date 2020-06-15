<?php

namespace spec\App\Service\Synchronizer;

use App\Entity\Product;
use App\Entity\TransactionChannel;
use App\Model\AvailabilitySource;
use App\Model\PartnerInterface;
use App\Model\ProductAvailabilityCollection;
use App\Repository\AvailabilityRepository;
use App\Service\BookingEngineManager;
use App\Service\Synchronizer\AvailabilityForcedAlignment;
use App\Service\Synchronizer\PriceSynchronizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class AvailabilitySynchronizerSpec
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class AvailabilityForcedAlignmentSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AvailabilityForcedAlignment::class);
    }

    function let(BookingEngineManager $bookingEngineManager, AvailabilityRepository $availabilityRepository)
    {
        $this->beConstructedWith($bookingEngineManager, $availabilityRepository);
    }

    public function it_only_supports_sync_of_availabilities()
    {
        $this->support(AvailabilityForcedAlignment::TYPE)->shouldBe(true);
        $this->support(PriceSynchronizer::TYPE)->shouldBe(false);
    }

    public function it_builds_collection_and_sync_data(
        PartnerInterface $partner,
        Product $product,
        Product $product1,
        AvailabilityRepository $availabilityRepository,
        BookingEngineManager $bookingEngineManager,
        ProductAvailabilityCollection $productAvailabilityCollection
    )
    {
        $start = date_create('2020-01-01');
        $end = date_create('2020-02-01');

        $partner->getProducts()->willReturn([$product, $product1]);
        $product->isMaster()->willReturn(true);
        $product1->isMaster()->willReturn(true);

        $bookingEngineManager->getAvailabilities($partner, Argument::type('datetime'), Argument::type('datetime'))->willReturn($productAvailabilityCollection);
        $productAvailabilityCollection->setSource(AvailabilitySource::ALIGNMENT)->shouldBeCalled()->willReturn($productAvailabilityCollection);
        $productAvailabilityCollection->isEmpty()->willReturn(false);
        $productAvailabilityCollection->setChannel(TransactionChannel::IRESA)->shouldBeCalled()->willReturn($productAvailabilityCollection);
        $bookingEngineManager->updateAvailability($productAvailabilityCollection)->shouldBeCalled();
        $availabilityRepository->clear()->shouldBeCalled();

        $this->sync($partner, $start, $end)->shouldBe([]);
    }
}
