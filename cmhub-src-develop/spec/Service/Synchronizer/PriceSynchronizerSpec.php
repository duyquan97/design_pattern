<?php

namespace spec\App\Service\Synchronizer;

use App\Entity\Transaction;
use App\Entity\TransactionChannel;
use App\Entity\TransactionStatus;
use App\Entity\TransactionType;
use App\Entity\Factory\TransactionFactory;
use App\Entity\Partner;
use App\Entity\Product;
use App\Repository\ProductRateRepository;
use App\Model\Factory\ProductRateCollectionFactory;
use App\Model\Factory\ProductRateFactory;
use App\Model\ProductRate;
use App\Entity\ProductRate as RateEntity;
use App\Model\ProductRateCollection;
use App\Model\Rate;
use App\Service\BookingEngineManager;
use App\Service\HubEngine\CmHubBookingEngine;
use App\Service\Iresa\IresaBookingEngine;
use App\Service\Synchronizer\AvailabilitySynchronizer;
use App\Service\Synchronizer\Diff\PriceDiff;
use App\Service\Synchronizer\PriceSynchronizer;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class PriceSynchronizerSpec
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class PriceSynchronizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PriceSynchronizer::class);
    }

    function let(BookingEngineManager $bookingEngineManager, PriceDiff $priceDiff) {
        $this->beConstructedWith($bookingEngineManager, $priceDiff);
    }

    function it_support_price_type()
    {
        $this->support(PriceSynchronizer::TYPE)->shouldBe(true);
        $this->support(AvailabilitySynchronizer::TYPE)->shouldBe(false);
    }

}
