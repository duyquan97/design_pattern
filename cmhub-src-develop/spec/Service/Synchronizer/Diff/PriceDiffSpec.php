<?php

namespace spec\App\Service\Synchronizer\Diff;

use App\Exception\IresaClientException;
use App\Model\Availability;
use App\Entity\ProductRate as RateEntity;
use App\Model\Factory\ProductRateCollectionFactory;
use App\Model\Factory\ProductRateFactory;
use App\Model\PartnerInterface;
use App\Model\ProductCollection;
use App\Model\ProductInterface;
use App\Model\ProductRate;
use App\Model\ProductRateCollection;
use App\Service\HubEngine\CmHubBookingEngine;
use App\Service\Iresa\IresaBookingEngine;
use App\Service\Loader\ProductLoader;
use App\Service\Synchronizer\Diff\PriceDiff;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;


/**
 * Class PriceDiffSpec
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class PriceDiffSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PriceDiff::class);
    }

    public function let(IresaBookingEngine $iresaBookingEngine, CmHubBookingEngine $cmHubBookingEngine, ProductRateFactory $productRateFactory, ProductRateCollectionFactory $productRateCollectionFactory, ProductLoader $productLoader)
    {
        $this->beConstructedWith($iresaBookingEngine, $cmHubBookingEngine, $productRateFactory, $productRateCollectionFactory, $productLoader);
    }

    public function it_finds_discrepancies(
        IresaBookingEngine $iresaBookingEngine,
        CmHubBookingEngine $cmHubBookingEngine,
        ProductRateFactory $productRateFactory,
        ProductRateCollectionFactory $productRateCollectionFactory,
        ProductRateCollection $productRateCollection,
        PartnerInterface $partner,
        ProductLoader $productLoader,
        ProductCollection $productCollection,
        ProductInterface $product,
        ProductInterface $product1,
        ProductRateCollection $cmhubRateCollection,
        ProductRateCollection $iresaRateCollection,
        ProductRate $productRate,
        ProductRate $productRate1,
        ProductRate $priceDiff,
        RateEntity $rate,
        RateEntity $rate1,
        RateEntity $rate2,
        RateEntity $rate3,
        RateEntity $iresaRate,
        RateEntity $iresaRate1,
        RateEntity $iresaRate2,
        RateEntity $iresaRate3
    )
    {
        $productRateCollectionFactory->create($partner)->willReturn($productRateCollection);
        $productLoader->getByPartner($partner)->willReturn($productCollection);

        $productCollection->toArray()->willReturn([
            $product,
            $product1
        ]);

        $cmHubBookingEngine
            ->getRates(
                $partner,
                Argument::type(\DateTime::class),
                Argument::type(\DateTime::class),
                [
                    $product,
                    $product1
                ]
            )
            ->willReturn($cmhubRateCollection);

        $iresaBookingEngine
            ->getRates($partner, Argument::type(\DateTime::class), Argument::type(\DateTime::class), [
                $product,
                $product1
            ])
            ->willReturn($iresaRateCollection);


        $cmhubRateCollection
            ->getProductRates()
            ->willReturn(
                [
                    $productRate,
                    $productRate1
                ]
            );

        $productRate->getProduct()->willReturn($product);
        $productRate1->getProduct()->willReturn($product1);

        $product->isMaster()->willReturn(true);
        $product1->isMaster()->willReturn(false);

        $productRateFactory->create($product)->shouldBeCalled()->willReturn($priceDiff);
        $productRateFactory->create($product1)->shouldNotBeCalled();

        $productRate->getRates()->willReturn([
            $rate,
            $rate1,
            $rate2,
            $rate3
        ]);
        $rate->getStart()->willReturn($date = date_create('2020-01-01'));
        $rate1->getStart()->willReturn($date1 = date_create('2020-01-02'));
        $rate2->getStart()->willReturn($date2 = date_create('2020-01-03'));
        $rate3->getStart()->willReturn($date3 = date_create('2020-01-04'));

        $iresaRateCollection->getByProductAndDate($product, $date)->willReturn($iresaRate);
        $iresaRateCollection->getByProductAndDate($product, $date1)->willReturn($iresaRate1);
        $iresaRateCollection->getByProductAndDate($product, $date2)->willReturn($iresaRate2);
        $iresaRateCollection->getByProductAndDate($product, $date3)->willReturn($iresaRate3);

        $iresaRate->getAmount()->willReturn(1);
        $rate->getAmount()->willReturn(2);
        $iresaRate1->getAmount()->willReturn(22);
        $rate1->getAmount()->willReturn(22);
        $iresaRate2->getAmount()->willReturn(22);
        $rate2->getAmount()->willReturn(22);
        $iresaRate3->getAmount()->willReturn(0);
        $rate3->getAmount()->willReturn(3);

        $priceDiff->addRate($rate)->shouldBeCalled();
        $priceDiff->addRate($rate1)->shouldNotBeCalled();
        $priceDiff->addRate($rate2)->shouldNotBeCalled();
        $priceDiff->addRate($rate3)->shouldBeCalled();

        $priceDiff->isEmpty()->willReturn(false);
        $productRateCollection->addProductRate($priceDiff)->shouldBeCalled();

        $this->diff($partner, date_create(), date_create('+1 day'))->shouldBe($productRateCollection);
    }

    public function it_throw_iresa_exception(
        IresaBookingEngine $iresaBookingEngine,
        CmHubBookingEngine $cmHubBookingEngine,
        ProductRateFactory $productRateFactory,
        ProductRateCollectionFactory $productRateCollectionFactory,
        ProductRateCollection $productRateCollection,
        PartnerInterface $partner,
        ProductLoader $productLoader,
        ProductCollection $productCollection,
        ProductInterface $product,
        ProductInterface $product1,
        ProductRateCollection $cmhubRateCollection
    )
    {
        $productRateCollectionFactory->create($partner)->willReturn($productRateCollection);
        $productLoader->getByPartner($partner)->willReturn($productCollection);

        $productCollection->toArray()->willReturn([
            $product,
            $product1
        ]);

        $cmHubBookingEngine
            ->getRates(
                $partner,
                Argument::type(\DateTime::class),
                Argument::type(\DateTime::class),
                [
                    $product,
                    $product1
                ]
            )
            ->willReturn($cmhubRateCollection);

        $iresaBookingEngine
            ->getRates($partner, Argument::type(\DateTime::class), Argument::type(\DateTime::class), [
                $product,
                $product1
            ])
            ->willThrow(IresaClientException::class);


        $cmhubRateCollection->getProductRates()->shouldNotBeCalled();
        $productRateFactory->create($product)->shouldNotBeCalled();
        $productRateFactory->create($product1)->shouldNotBeCalled();

        $this->shouldThrow(IresaClientException::class)->during('diff', [$partner, date_create(), date_create('+1 day')]);
    }
}
