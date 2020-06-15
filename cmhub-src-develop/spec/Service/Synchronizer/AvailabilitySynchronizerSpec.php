<?php

namespace spec\App\Service\Synchronizer;

use App\Entity\TransactionChannel;
use App\Model\PartnerInterface;
use App\Model\ProductAvailabilityCollection;
use App\Service\BookingEngineManager;
use App\Service\Synchronizer\AvailabilitySynchronizer;
use App\Service\Synchronizer\Diff\AvailabilityDiff;
use App\Service\Synchronizer\PriceSynchronizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class AvailabilitySynchronizerSpec
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class AvailabilitySynchronizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AvailabilitySynchronizer::class);
    }

    function let(BookingEngineManager $bookingEngineManager, AvailabilityDiff $availabilityDiff)
    {
        $this->beConstructedWith($availabilityDiff, $bookingEngineManager);
    }

    public function it_only_supports_sync_of_availabilities()
    {
        $this->support(PriceSynchronizer::TYPE)->shouldBe(false);
        $this->support(AvailabilitySynchronizer::TYPE)->shouldBe(true);
    }

    public function it_builds_collection_and_sync_data(
        PartnerInterface $partner,
        BookingEngineManager $bookingEngineManager,
        AvailabilityDiff $availabilityDiff,
        ProductAvailabilityCollection $productAvailabilityCollectionUpdated,
        ProductAvailabilityCollection $productAvailabilityCollection,
        ProductAvailabilityCollection $productAvailabilityCollectionUpdated1,
        ProductAvailabilityCollection $productAvailabilityCollection1,
        ProductAvailabilityCollection $productAvailabilityCollection2
    )
    {
        $start = date_create('2020-01-01');
        $end = date_create('2020-03-01');

        $availabilityDiff
            ->diff(
                $partner,
                Argument::that(function (\DateTime $start) {
                    return $start->format('Y-m-d') === '2020-01-01';
                }),
                Argument::that(function (\DateTime $end) {
                    return $end->format('Y-m-d') === '2020-02-01';
                })
            )
            ->willReturn($productAvailabilityCollection);
        $productAvailabilityCollection->isEmpty()->willReturn(false);
        $productAvailabilityCollection->setChannel(TransactionChannel::IRESA)->shouldBeCalled()->willReturn($productAvailabilityCollection);
        $bookingEngineManager->updateAvailability($productAvailabilityCollection)->shouldBeCalled()->willReturn($productAvailabilityCollectionUpdated);

        $availabilityDiff
            ->diff(
                $partner,
                Argument::that(function (\DateTime $startDate) {
                    return $startDate->format('Y-m-d') === '2020-02-01';
                }),
                Argument::that(function (\DateTime $endDate) {
                    return $endDate->format('Y-m-d') === '2020-03-01';
                })
            )
            ->willReturn($productAvailabilityCollection1);

        $productAvailabilityCollection1->isEmpty()->willReturn(false);
        $productAvailabilityCollection1->setChannel(TransactionChannel::IRESA)->shouldBeCalled()->willReturn($productAvailabilityCollection1);
        $bookingEngineManager->updateAvailability($productAvailabilityCollection1)->shouldBeCalled()->willReturn($productAvailabilityCollectionUpdated1);

        $availabilityDiff
            ->diff(
                $partner,
                Argument::that(function (\DateTime $startDate) {
                    return $startDate->format('Y-m-d') === '2020-03-01';
                }),
                Argument::that(function (\DateTime $endDate) {
                    return $endDate->format('Y-m-d') === '2020-04-01';
                })
            )
            ->willReturn($productAvailabilityCollection2);

        $productAvailabilityCollection2->isEmpty()->willReturn(true);
        $bookingEngineManager->updateAvailability($productAvailabilityCollection2)->shouldNotBeCalled();


        $this->sync($partner, $start, $end)->shouldBe([
            $productAvailabilityCollectionUpdated,
            $productAvailabilityCollectionUpdated1
        ]);
    }
}
