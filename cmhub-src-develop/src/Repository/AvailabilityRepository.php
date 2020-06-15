<?php

namespace App\Repository;

use App\Entity\Availability;
use App\Entity\Partner;
use App\Entity\Product;
use App\Entity\Transaction;
use App\Entity\TransactionStatus;
use App\Model\PartnerInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Internal\Hydration\IterableResult;

/**
 * Class AvailabilityRepository
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 *
 * @codeCoverageIgnore
 */
class AvailabilityRepository extends ServiceEntityRepository
{
    /**
     * AvailabilityRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Availability::class);
    }

    /**
     *
     * @param Partner   $partner
     * @param \DateTime $start
     * @param \DateTime $end
     * @param array     $products
     *
     * @return Availability[]
     */
    public function findByDateRange(?Partner $partner, \DateTime $start, \DateTime $end, array $products = [])
    {
        $queryBuilder = $this
            ->createQueryBuilder('availability')
            ->where('availability.date >= :start')
            ->andWhere('availability.date <= :end')
            ->setParameter('start', $start->setTime(0, 0, 0))
            ->setParameter('end', $end->setTime(0, 0, 0))
            ->orderBy('availability.date', 'asc');

        if ($partner) {
            $queryBuilder
                ->andWhere('availability.partner = :partner')
                ->setParameter('partner', $partner);
        }

        if (sizeof($products) > 0) {
            $queryBuilder
                ->andWhere('availability.product IN (:products)')
                ->setParameter('products', $products);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param PartnerInterface $partner
     *
     * @return IterableResult
     */
    public function getScheduledAvailabilitiesForPartner(PartnerInterface $partner): IterableResult
    {
        return $this
            ->createQueryBuilder('availability')
            ->innerJoin('availability.transaction', 'transaction')
            ->where('transaction.status = :status')
            ->andWhere('availability.partner = :partner')
            ->setParameter('status', TransactionStatus::SCHEDULED)
            ->setParameter('partner', $partner)
            ->getQuery()
            ->iterate();
    }


    /**
     *
     * @param Product $product
     *
     * @return void
     */
    public function updatePartner(Product $product)
    {
        $query = $this->createQueryBuilder('a');
        $query
            ->update()
            ->set('a.partner', ':partner')
            ->where('a.product = :product')
            ->setParameter('partner', $product->getPartner())
            ->setParameter('product', $product);

        $query->getQuery()->execute();
    }

    /**
     * @param Transaction $transaction
     *
     * @return mixed
     */
    public function findByTransactionAndMasterProduct(Transaction $transaction)
    {
        return $this
            ->createQueryBuilder('a')
            ->innerJoin('a.product', 'p')
            ->where('a.transaction = :transaction')
            ->andWhere('p.masterProduct IS NULL')
            ->setParameter('transaction', $transaction)
            ->getQuery()
            ->getResult();
    }

    /**
     *
     * @param Product $product
     *
     * @return void
     */
    public function reset(Product $product)
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->update()
            ->set('a.stock', 0)
            ->set('a.stopSale', ':stopSale')
            ->set('a.updatedAt', ':now')
            ->where('a.date >= :now')
            ->andWhere('a.product = :product')
            ->setParameter('product', $product)
            ->setParameter('now', date_create())
            ->setParameter('stopSale', false)
            ->getQuery()
            ->execute();
    }

    /**
     * @param Product $product
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findByProductAndDate(Product $product, \DateTime $startDate, \DateTime $endDate)
    {
        $sql = 'SELECT date, stock as quantity from availability WHERE product_id = ? AND date >= ? AND date <= ? AND stock > 0 and stop_sale = 0 ORDER BY date ASC';
        $stmt = $this->_em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $product->getId(), ParameterType::INTEGER);
        $stmt->bindValue(2, $startDate->format('Y-m-d'));
        $stmt->bindValue(3, $endDate->format('Y-m-d'));
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_FUNC, function ($date, $quantity) {
            return [
                'date' => $date,
                'quantity' => intval($quantity),
            ];
        });
    }
}
