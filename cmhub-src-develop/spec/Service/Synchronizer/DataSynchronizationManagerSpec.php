<?php

namespace spec\App\Service\Synchronizer;

use App\Exception\SynchronizerNotFoundException;
use App\Model\PartnerInterface;
use App\Service\Synchronizer\AvailabilitySynchronizer;
use App\Service\Synchronizer\DataSynchronizationManager;
use App\Service\Synchronizer\PriceSynchronizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DataSynchronizationManagerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(DataSynchronizationManager::class);
    }

    function let(AvailabilitySynchronizer $synchronizer1, PriceSynchronizer $synchronizer2)
    {
        $this->beConstructedWith([$synchronizer1, $synchronizer2]);
    }

    function it_sync(PartnerInterface $partner, AvailabilitySynchronizer $synchronizer1, PriceSynchronizer $synchronizer2)
    {
        $start = new \DateTime();
        $end = new \DateTime('+1 day');
        $type = 'availability';
        $synchronizer1->support($type)->willReturn(true);
        $synchronizer2->support($type)->willReturn(false);
        $synchronizer1->sync($partner, $start, $end)->shouldBeCalled()->willReturn([]);

        $this->sync($partner, $start, $end, $type);
    }

    function it_does_not_sync(PartnerInterface $partner, AvailabilitySynchronizer $synchronizer1, PriceSynchronizer $synchronizer2)
    {
        $start = new \DateTime();
        $end = new \DateTime('+1 day');
        $type = 'availability';
        $synchronizer1->support($type)->willReturn(false);
        $synchronizer2->support($type)->willReturn(false);

        $this->shouldThrow(SynchronizerNotFoundException::class)->during('sync', [$partner, $start, $end, $type]);
    }

}
