<?php

namespace App\Tests\Repository;

use App\Entity\Booking;
use App\Entity\ChannelManager;
use App\Entity\Partner;
use App\Model\OTADateType;
use App\Repository\BookingRepository;
use App\Repository\ChannelManagerRepository;
use App\Service\ChannelManager\ChannelManagerList;
use App\Service\Loader\PartnerLoader;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BookingRepositoryTest extends KernelTestCase
{
    public function testFindByDateRangeCreateDateWithCM()
    {
        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)->disableOriginalConstructor()
            ->setMethods(['join', 'andWhere', 'setParameters', 'getQuery', 'getResult'])->getMock();


        $queryBuilder->method('join')->willReturnCallback(
            function ($field, $alias) use ($queryBuilder) {
                $this->assertSame('booking.partner', $field);
                $this->assertSame('partner', $alias);

                return $queryBuilder;
            }
        );

        $queryBuilder->method('andWhere')->willReturnCallback(
            function ($cond) use ($queryBuilder) {
                switch ($cond) {
                    case 'booking.processed = :processed':
                        $this->assertSame('booking.processed = :processed', $cond);
                        break;
                    case 'partner.enabled = :enabled':
                        $this->assertSame('partner.enabled = :enabled', $cond);
                        break;
                    case 'booking.createDate >= :start':
                        $this->assertSame('booking.createDate >= :start', $cond);
                        break;
                    case 'booking.createDate <= :end':
                        $this->assertSame('booking.createDate <= :end', $cond);
                        break;
                    case 'partner.channelManager = :channelManager':
                        $this->assertSame('partner.channelManager = :channelManager', $cond);
                        break;
                    case 'booking.partner IN (:partners)':
                        $this->assertSame('booking.partner IN (:partners)', $cond);
                        break;
                }

                return $queryBuilder;
            }
        );
        $queryBuilder->method('setParameters')->willReturnCallback(
            function ($value) use ($queryBuilder) {
                $this->assertSame(true, $value['processed']);
                $this->assertSame(true, $value['enabled']);
                $this->assertSame('2019-05-01', $value['start']->format('Y-m-d'));
                $this->assertSame('2019-05-03', $value['end']->format('Y-m-d'));
                $this->assertInstanceOf(ChannelManager::class, $value['channelManager']);
                $this->assertSame(2, $value['channelManager']->getId());
                $this->assertInstanceOf(Partner::class, $value['partners'][0]);
                $this->assertSame('00145205', $value['partners'][0]->getIdentifier());

                return $queryBuilder;
            }
        );

        $queryBuilder->method('getQuery')->willReturnSelf();

        $repositoryMock = $this->getMockBuilder(BookingRepository::class)->disableOriginalConstructor()
            ->setMethods(['createQueryBuilder'])->getMock();

        $repositoryMock->method('createQueryBuilder')->willReturnCallback(
            function ($alias) use ($queryBuilder) {
                $this->assertSame('booking', $alias);

                return $queryBuilder;
            }
        );

        $start = new \DateTime('2019-05-01');
        $end = new \DateTime('2019-05-03');

        static::bootKernel();
        $entityManager = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        $partnerLoader = new PartnerLoader($entityManager);
        $partner = $partnerLoader->find('00145205');
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')
            ->with(ChannelManager::class)
            ->willReturn($entityManager);

        $channelManagerRepository = new ChannelManagerRepository($registry);
        $channelManager = $channelManagerRepository->find(2);
        $dateType = OTADateType::CREATE_DATE;

        $repositoryMock->findByDateRange($start, $end, [$partner], $channelManager, $dateType);
    }

    public function testFindByDateRangeCreateDateWithOutCM()
    {
        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)->disableOriginalConstructor()
            ->setMethods(['join', 'andWhere', 'setParameters', 'getQuery', 'getResult'])->getMock();


        $queryBuilder->method('join')->willReturnCallback(
            function ($field, $alias) use ($queryBuilder) {
                $this->assertSame('booking.partner', $field);
                $this->assertSame('partner', $alias);

                return $queryBuilder;
            }
        );

        $queryBuilder->method('andWhere')->willReturnCallback(
            function ($cond) use ($queryBuilder) {
                switch ($cond) {
                    case 'booking.processed = :processed':
                        $this->assertSame('booking.processed = :processed', $cond);
                        break;
                    case 'partner.enabled = :enabled':
                        $this->assertSame('partner.enabled = :enabled', $cond);
                        break;
                    case 'booking.createDate >= :start':
                        $this->assertSame('booking.createDate >= :start', $cond);
                        break;
                    case 'booking.createDate <= :end':
                        $this->assertSame('booking.createDate <= :end', $cond);
                        break;
                    case 'booking.partner IN (:partners)':
                        $this->assertSame('booking.partner IN (:partners)', $cond);
                        break;
                }

                return $queryBuilder;
            }
        );
        $queryBuilder->method('setParameters')->willReturnCallback(
            function ($value) use ($queryBuilder) {
                $this->assertSame(true, $value['processed']);
                $this->assertSame(true, $value['enabled']);
                $this->assertSame('2019-05-01', $value['start']->format('Y-m-d'));
                $this->assertSame('2019-05-03', $value['end']->format('Y-m-d'));
                $this->assertInstanceOf(Partner::class, $value['partners'][0]);
                $this->assertSame('00145205', $value['partners'][0]->getIdentifier());

                return $queryBuilder;
            }
        );

        $queryBuilder->method('getQuery')->willReturnSelf();

        $repositoryMock = $this->getMockBuilder(BookingRepository::class)->disableOriginalConstructor()
            ->setMethods(['createQueryBuilder'])->getMock();

        $repositoryMock->method('createQueryBuilder')->willReturnCallback(
            function ($alias) use ($queryBuilder) {
                $this->assertSame('booking', $alias);

                return $queryBuilder;
            }
        );

        $start = new \DateTime('2019-05-01');
        $end = new \DateTime('2019-05-03');

        static::bootKernel();
        $entityManager = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        $partnerLoader = new PartnerLoader($entityManager);
        $partner = $partnerLoader->find('00145205');
        $channelManager = null;
        $dateType = OTADateType::CREATE_DATE;

        $repositoryMock->findByDateRange($start, $end, [$partner], $channelManager, $dateType);
    }

    public function testFindByDateRange()
    {
        $start = new \DateTime('2019-03-14');
        $end = new \DateTime('2019-03-18');

        static::bootKernel();
        $entityManager = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        $partnerLoader = new PartnerLoader($entityManager);
        $partner = $partnerLoader->find('00145205');
        $channelManager = null;
        $dateType = OTADateType::CREATE_DATE;

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')
            ->with(Booking::class)
            ->willReturn($entityManager);

        $bookingRepository = new BookingRepository($registry);
        $bookings = $bookingRepository->findByDateRange($start, $end, [$partner], $channelManager, $dateType);
        $this->assertCount(2, $bookings);
    }

    public function testFindByDateRangeWithWrongCM()
    {
        $start = new \DateTime('2019-03-14');
        $end = new \DateTime('2019-03-18');

        static::bootKernel();
        $entityManager = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        $partnerLoader = new PartnerLoader($entityManager);
        $partner = $partnerLoader->find('00145205');
        $registryCM = $this->createMock(ManagerRegistry::class);
        $registryCM->method('getManagerForClass')
            ->with(ChannelManager::class)
            ->willReturn($entityManager);

        $channelManagerRepository = new ChannelManagerRepository($registryCM);
        $channelManager = $channelManagerRepository->findOneBy(['identifier' => ChannelManagerList::BB8]);
        $dateType = OTADateType::CREATE_DATE;

        $registryBooking = $this->createMock(ManagerRegistry::class);
        $registryBooking->method('getManagerForClass')
            ->with(Booking::class)
            ->willReturn($entityManager);

        $bookingRepository = new BookingRepository($registryBooking);
        $bookings = $bookingRepository->findByDateRange($start, $end, [$partner], $channelManager, $dateType);
        $this->assertCount(0, $bookings);
    }

    public function testFindByDateRangeWithRightCM()
    {
        $start = new \DateTime('2019-03-14');
        $end = new \DateTime('2019-03-18');

        static::bootKernel();
        $entityManager = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        $partnerLoader = new PartnerLoader($entityManager);
        $partner = $partnerLoader->find('00145205');
        $registryCM = $this->createMock(ManagerRegistry::class);
        $registryCM->method('getManagerForClass')
            ->with(ChannelManager::class)
            ->willReturn($entityManager);

        $channelManagerRepository = new ChannelManagerRepository($registryCM);
        $channelManager = $channelManagerRepository->findOneBy(['identifier' => ChannelManagerList::TRAVELCLICK]);
        $dateType = OTADateType::CREATE_DATE;

        $registryBooking = $this->createMock(ManagerRegistry::class);
        $registryBooking->method('getManagerForClass')
            ->with(Booking::class)
            ->willReturn($entityManager);

        $bookingRepository = new BookingRepository($registryBooking);
        $bookings = $bookingRepository->findByDateRange($start, $end, [$partner], $channelManager, $dateType);
        $this->assertCount(2, $bookings);
        $this->assertEquals('00145205', $bookings[0]->getPartner()->getIdentifier());
        $this->assertGreaterThan(new \DateTime('2019-03-14'), $bookings[0]->getCreatedAt());
        $this->assertLessThan(new \DateTime('2019-03-18'), $bookings[0]->getCreatedAt());
    }
}
