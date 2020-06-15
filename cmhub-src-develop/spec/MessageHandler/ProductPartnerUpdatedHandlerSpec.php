<?php

namespace spec\App\MessageHandler;

use App\Entity\Product;
use App\Message\ProductPartnerUpdated;
use App\MessageHandler\ProductPartnerUpdatedHandler;
use App\Repository\AvailabilityRepository;
use App\Repository\ProductRateRepository;
use App\Service\Loader\ProductLoader;
use PhpSpec\ObjectBehavior;

class ProductPartnerUpdatedHandlerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductPartnerUpdatedHandler::class);
    }

    function let(
        ProductRateRepository $productRateRepository, AvailabilityRepository $availabilityRepository, ProductLoader $productLoader
    )
    {
        $this->beConstructedWith( $productRateRepository, $availabilityRepository, $productLoader);
    }

    function it_process_message(
        ProductPartnerUpdated $message,
        ProductLoader $productLoader,
        Product $product,
        ProductRateRepository $productRateRepository,
        AvailabilityRepository $availabilityRepository
    )
    {
        $message->getIdentifier()->willReturn('12345');
        $productLoader->getProductByIdentifier('12345')->willReturn($product);
        $productRateRepository->updatePartner($product)->shouldBeCalled();
        $availabilityRepository->updatePartner($product)->shouldBeCalled();

        $this->__invoke($message);
    }
}
