<?php

namespace spec\App\Service\Loader;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Model\PartnerInterface;
use App\Model\ProductCollection;
use App\Model\ProductInterface;
use App\Service\Loader\ProductLoader;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;

class ProductLoaderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductLoader::class);
    }

    function let(ProductRepository $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_finds_products_by_room_code(ProductInterface $product, PartnerInterface $partner, ProductRepository $repository)
    {
        $repository->findOneBy(
            [
                'partner'    => $partner,
                'identifier' => 'ROOMID1',
                'reservable' => true,
                'masterProduct' => NULL,
            ]
        )
                   ->willReturn($product);

        $this->find($partner, 'ROOMID1')->shouldBe($product);
    }

    function it_finds_products_by_partner(ProductRepository $repository, PartnerInterface $partner, ProductInterface $product, ProductInterface $product1)
    {
        $repository
            ->findBy(
                [
                    'partner'    => $partner,
                    'reservable' => true,
                    'masterProduct' => NULL,
                ]
            )
            ->willReturn(
                $result = [
                    $product,
                    $product1
                ]
            );

        //TODO: Improve test moking product collection using factory or injecting it
        $this->getByPartner($partner)->shouldBeAnInstanceOf(ProductCollection::class);
    }

    function it_finds_products_by_partner_and_updated_date(ProductRepository $repository, QueryBuilder $queryBuilder, AbstractQuery $query, PartnerInterface $partner, ProductInterface $product, ProductInterface $product1, \DateTime $dateTime)
    {
        $dateTime->format('Y-m-d H:i:s')->willReturn('2019-20-12 12:30:29');
        $repository->createQueryBuilder('product')->willReturn($queryBuilder);
        $queryBuilder->andWhere('product.updatedAt >= :dateFrom')->willReturn($queryBuilder);
        $queryBuilder->setParameter('dateFrom', '2019-20-12 12:30:29')->willReturn($queryBuilder);
        $queryBuilder->andWhere('product.partner IN (:partnerIds)')->willReturn($queryBuilder);
        $queryBuilder->andWhere('product.masterProduct IS NULL')->willReturn($queryBuilder);
        $queryBuilder->setParameter('partnerIds', [$partner])->willReturn($queryBuilder);

        $queryBuilder->getQuery()->willReturn($query);
        $query->getResult()->willReturn(
            [
                $product,
                $product1
            ]
        );

        $this->getByUpdatedDate($dateTime, [$partner])->shouldBeAnInstanceOf(ProductCollection::class);
    }

    function it_finds_products_by_updated_date(ProductRepository $repository, QueryBuilder $queryBuilder, AbstractQuery $query, ProductInterface $product, ProductInterface $product1, \DateTime $dateTime)
    {
        $dateTime->format('Y-m-d H:i:s')->willReturn('2019-20-12 12:30:29');
        $repository->createQueryBuilder('product')->willReturn($queryBuilder);
        $queryBuilder->andWhere('product.updatedAt >= :dateFrom')->willReturn($queryBuilder);
        $queryBuilder->setParameter('dateFrom', '2019-20-12 12:30:29')->willReturn($queryBuilder);
        $queryBuilder->andWhere('product.masterProduct IS NULL')->willReturn($queryBuilder);

        $queryBuilder->getQuery()->willReturn($query);
        $query->getResult()->willReturn(
            [
                $product,
                $product1
            ]
        );

        $this->getByUpdatedDate($dateTime, null)->shouldBeAnInstanceOf(ProductCollection::class);
    }

    function it_finds_products_by_room_codes_param(ProductInterface $product1, ProductInterface $product2, PartnerInterface $partner, ProductRepository $repository)
    {
        $repository->findBy(
            [
                'partner'    => $partner,
                'identifier' => [
                    'ROOMID1',
                    'ROOMID2'
                ],
                'reservable' => true,
            ]
        )
            ->willReturn([$product1, $product2]);

        $this->getProductsByRoomCode($partner, ['ROOMID1','ROOMID2'])->shouldBe([$product1, $product2]);
    }

    function it_finds_product_by_identifer(ProductInterface $product1, ProductRepository $repository)
    {
        $repository->findOneBy(
            [
                'identifier' => 'ROOMID1'
            ]
        )
            ->willReturn($product1);

        $this->getProductByIdentifier('ROOMID1')->shouldBe($product1);
    }
}
