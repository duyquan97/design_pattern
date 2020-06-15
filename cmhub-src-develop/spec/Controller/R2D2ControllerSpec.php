<?php

namespace spec\App\Controller;

use App\Controller\R2D2Controller;
use App\Entity\Product;
use App\Exception\CmHubException;
use App\Repository\AvailabilityRepository;
use App\Repository\ProductRateRepository;
use App\Repository\ProductRepository;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class R2D2ControllerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(R2D2Controller::class);
    }

    function let(AvailabilityRepository $availabilityRepository, ProductRateRepository $productRateRepository, ProductRepository $productRepository, CmhubLogger $logger)
    {
        $this->beConstructedWith($availabilityRepository, $productRateRepository, $productRepository, $logger);
    }

    function it_does_not_find_product(ProductRepository $productRepository, Request $request, CmhubLogger $logger)
    {
        $id = 'id';
        $productRepository->findOneBy(['identifier' => $id])->willReturn(null);
        $exception = new NotFoundHttpException(sprintf('Product identifier "%s" is not found in the system', $id));
        $logger->addOperationException(LogAction::R2D2_GET_AVAILABILITY, $exception, $this)->shouldBeCalled();

        $this->shouldThrow(NotFoundHttpException::class)->during('getAvailabilityAction', [$request, $id]);
    }

    function it_does_not_get_start_date(ProductRepository $productRepository, Request $request, ParameterBag $query, Product $product, CmhubLogger $logger)
    {
        $id = 'id';
        $productRepository->findOneBy(['identifier' => $id])->willReturn($product);
        $request->query = $query;

        $query->get('start')->willReturn(null);
        $query->get('end')->willReturn('2019-03-19');

        $exception = new BadRequestHttpException('Either start date or end date is missing');
        $logger->addOperationException(LogAction::R2D2_GET_AVAILABILITY, $exception, $this)->shouldBeCalled();

        $this->shouldThrow(BadRequestHttpException::class)->during('getAvailabilityAction', [$request, $id]);
    }

    function it_get_wrong_date_format(ProductRepository $productRepository, Request $request, ParameterBag $query, Product $product, CmhubLogger $logger)
    {
        $id = 'id';
        $productRepository->findOneBy(['identifier' => $id])->willReturn($product);
        $request->query = $query;

        $query->get('start')->willReturn('2019');
        $query->get('end')->willReturn('2019-03-19');

        $exception = new BadRequestHttpException('Wrong date format. Expected format is "Y-m-d"');
        $logger->addOperationException(LogAction::R2D2_GET_AVAILABILITY, $exception, $this)->shouldBeCalled();

        $this->shouldThrow(BadRequestHttpException::class)->during('getAvailabilityAction', [$request, $id]);
    }

    function it_throws_dbal_exception(AvailabilityRepository $availabilityRepository, ProductRepository $productRepository, Request $request, ParameterBag $query, Product $product, CmhubLogger $logger)
    {
        $id = 'id';
        $productRepository->findOneBy(['identifier' => $id])->willReturn($product);
        $request->query = $query;
        $query->get('start')->willReturn('2019-03-16');
        $query->get('end')->willReturn('2019-03-19');
        $exception = new \Doctrine\DBAL\DBALException('Server Error', 500);
        $availabilityRepository->findByProductAndDate(
            $product,
            Argument::that(function(\DateTime $start) {
                return $start->format('Y-m-d') === '2019-03-16';
            }),
            Argument::that(function(\DateTime $start) {
                return $start->format('Y-m-d') === '2019-03-19';
            })
        )->willThrow($exception);

        $logger->addOperationException(LogAction::R2D2_GET_AVAILABILITY, $exception, $this)->shouldBeCalled();

        $this->shouldThrow(CmHubException::class)->during('getAvailabilityAction', [$request, $id]);
    }

    function it_get_availabilities(AvailabilityRepository $availabilityRepository, ProductRepository $productRepository, Request $request, ParameterBag $query, Product $product)
    {
        $id = 'id';
        $result = [
            ['date' => '2019-03-16', 'quantity' => 20],
            ['date' => '2019-03-17', 'quantity' => 20],
            ['date' => '2019-03-18', 'quantity' => 20],
            ['date' => '2019-03-19', 'quantity' => 20],
        ];
        $productRepository->findOneBy(['identifier' => $id])->willReturn($product);
        $request->query = $query;
        $query->get('start')->willReturn('2019-03-16');
        $query->get('end')->willReturn('2019-03-19');
        $availabilityRepository->findByProductAndDate(
            $product,
            Argument::that(function(\DateTime $start) {
                return $start->format('Y-m-d') === '2019-03-16';
            }),
            Argument::that(function(\DateTime $start) {
                return $start->format('Y-m-d') === '2019-03-19';
            })
        )->willReturn($result);

        $this->getAvailabilityAction($request, $id)->shouldBe($result);
    }

    function it_does_not_find_product_when_get_price(ProductRepository $productRepository, Request $request, CmhubLogger $logger)
    {
        $id = 'id';
        $productRepository->findOneBy(['identifier' => $id])->willReturn(null);
        $exception = new NotFoundHttpException(sprintf('Product identifier "%s" is not found in the system', $id));
        $logger->addOperationException(LogAction::R2D2_GET_PRICE, $exception, $this)->shouldBeCalled();

        $this->shouldThrow(NotFoundHttpException::class)->during('getPriceAction', [$request, $id]);
    }

    function it_does_not_get_start_date_when_get_price(ProductRepository $productRepository, Request $request, ParameterBag $query, Product $product, CmhubLogger $logger)
    {
        $id = 'id';
        $productRepository->findOneBy(['identifier' => $id])->willReturn($product);
        $request->query = $query;

        $query->get('start')->willReturn(null);
        $query->get('end')->willReturn('2019-03-19');

        $exception = new BadRequestHttpException('Either start date or end date is missing');
        $logger->addOperationException(LogAction::R2D2_GET_PRICE, $exception, $this)->shouldBeCalled();

        $this->shouldThrow(BadRequestHttpException::class)->during('getPriceAction', [$request, $id]);
    }

    function it_get_wrong_date_format_when_get_price(ProductRepository $productRepository, Request $request, ParameterBag $query, Product $product, CmhubLogger $logger)
    {
        $id = 'id';
        $productRepository->findOneBy(['identifier' => $id])->willReturn($product);
        $request->query = $query;

        $query->get('start')->willReturn('2019');
        $query->get('end')->willReturn('2019-03-19');

        $exception = new BadRequestHttpException('Wrong date format. Expected format is "Y-m-d"');
        $logger->addOperationException(LogAction::R2D2_GET_PRICE, $exception, $this)->shouldBeCalled();

        $this->shouldThrow(BadRequestHttpException::class)->during('getPriceAction', [$request, $id]);
    }

    function it_throws_dbal_exception_when_get_price(ProductRateRepository $productRateRepository, ProductRepository $productRepository, Request $request, ParameterBag $query, Product $product, CmhubLogger $logger)
    {
        $id = 'id';
        $productRepository->findOneBy(['identifier' => $id])->willReturn($product);
        $request->query = $query;
        $query->get('start')->willReturn('2019-03-16');
        $query->get('end')->willReturn('2019-03-19');
        $exception = new \Doctrine\DBAL\DBALException('Server Error', 500);
        $productRateRepository->findByProductAndDate(
            $product,
            Argument::that(function(\DateTime $start) {
                return $start->format('Y-m-d') === '2019-03-16';
            }),
            Argument::that(function(\DateTime $start) {
                return $start->format('Y-m-d') === '2019-03-19';
            })
        )->willThrow($exception);

        $logger->addOperationException(LogAction::R2D2_GET_PRICE, $exception, $this)->shouldBeCalled();

        $this->shouldThrow(CmHubException::class)->during('getPriceAction', [$request, $id]);
    }

    function it_get_prices(ProductRateRepository $productRateRepository, ProductRepository $productRepository, Request $request, ParameterBag $query, Product $product)
    {
        $id = 'id';
        $result = [
            ['date' => '2019-03-16', 'quantity' => 20],
            ['date' => '2019-03-17', 'quantity' => 20],
            ['date' => '2019-03-18', 'quantity' => 20],
            ['date' => '2019-03-19', 'quantity' => 20],
        ];
        $productRepository->findOneBy(['identifier' => $id])->willReturn($product);
        $request->query = $query;
        $query->get('start')->willReturn('2019-03-16');
        $query->get('end')->willReturn('2019-03-19');
        $productRateRepository->findByProductAndDate(
            $product,
            Argument::that(function(\DateTime $start) {
                return $start->format('Y-m-d') === '2019-03-16';
            }),
            Argument::that(function(\DateTime $start) {
                return $start->format('Y-m-d') === '2019-03-19';
            })
        )->willReturn($result);

        $this->getPriceAction($request, $id)->shouldBe($result);
    }
}
