<?php

namespace spec\App\Booking\Processor;

use App\Model\BookingInterface;
use App\Model\BookingProductInterface;
use App\Model\ProductInterface;
use App\Booking\Processor\BookingSetMasterProductProcessor;
use PhpSpec\ObjectBehavior;

class BookingSetMasterProductProcessorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BookingSetMasterProductProcessor::class);
    }

    function it_sets_master_product_to_booking(
        BookingInterface $booking,
        BookingProductInterface $bookingProduct,
        BookingProductInterface $bookingProduct1,
        ProductInterface $product,
        ProductInterface $product1,
        ProductInterface $masterProduct
    )
    {
        $booking->getBookingProducts()->willReturn([
            $bookingProduct,
            $bookingProduct1
        ]);
        $bookingProduct->getProduct()->willReturn($product);
        $product->isMaster()->willReturn(false);
        $bookingProduct1->getProduct()->willReturn($product1);
        $product1->isMaster()->willReturn(true);

        $product->getMasterProduct()->willReturn($masterProduct);
        $bookingProduct->setProduct($masterProduct)->shouldBeCalled();
        $bookingProduct1->setProduct($masterProduct)->shouldNotBeCalled();

        $this->process($booking)->shouldBe($booking);
    }
}
