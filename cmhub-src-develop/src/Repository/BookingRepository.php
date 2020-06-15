<?php

namespace App\Repository;

use App\Entity\Booking;
use App\Entity\ChannelManager;
use App\Entity\Partner;
use App\Model\OTADateType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class BookingRepository
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 *
 * @codeCoverageIgnore
 */
class BookingRepository extends ServiceEntityRepository
{
    /**
     * BookingRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    /**
     *
     * @param string $identifier
     *
     * @return Booking|null
     */
    public function findOneByIdentifier(string $identifier)
    {
        return $this->findOneBy(['identifier' => $identifier]);
    }

    /**
     * Find bookings by
     *
     * @param \DateTime           $start
     * @param \DateTime           $end
     * @param array               $partners
     * @param ChannelManager|null $channelManager
     * @param string              $dateType
     *
     * @return Booking[]
     */
    public function findByDateRange(\DateTime $start, \DateTime $end, array $partners = array(), ChannelManager $channelManager = null, string $dateType = null)
    {
        $queryBuilder = $this
            ->createQueryBuilder('booking')
            ->join('booking.partner', 'partner')
            ->andWhere('booking.processed = :processed')
            ->andWhere('partner.enabled = :enabled');

        $parameters['processed'] = true;
        $parameters['enabled'] = true;

        if ($channelManager) {
            $queryBuilder
                ->andWhere('partner.channelManager = :channelManager');
            $parameters['channelManager'] = $channelManager;
        }

        switch ($dateType) {
            case OTADateType::CREATE_DATE:
                $queryBuilder
                    ->andwhere('booking.createdAt >= :start')
                    ->andWhere('booking.createdAt <= :end');
                break;
            case OTADateType::ARRIVAL_DATE:
                $start->setTime(0, 0);
                $end->setTime(0, 0);
                $queryBuilder
                    ->andWhere('booking.startDate >= :start')
                    ->andWhere('booking.startDate <= :end');
                break;
            case OTADateType::DEPARTURE_DATE:
                $start->setTime(0, 0);
                $end->setTime(0, 0);
                $queryBuilder
                    ->andWhere('booking.endDate >= :start')
                    ->andWhere('booking.endDate <= :end');
                break;
            default:
                $queryBuilder
                    ->andwhere('booking.updatedAt >= :start')
                    ->andWhere('booking.updatedAt <= :end');
        }
        $parameters['start'] = $start;
        $parameters['end'] = $end;

        if ($partners) {
            $queryBuilder
                ->andWhere('booking.partner IN (:partners)');
            $parameters['partners'] = $partners;
        }

        $queryBuilder->setParameters($parameters);

        return $queryBuilder->getQuery()->getResult();
    }
}
