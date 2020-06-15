<?php

namespace App\Repository;

use App\Entity\Partner;
use App\Entity\Product;
use App\Entity\ProductRate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\ParameterType;

/**
 * Class ProductRateRepository
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 *
 * @codeCoverageIgnore
 */
class ProductRateRepository extends ServiceEntityRepository
{
    /**
     * ProductRateRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductRate::class);
    }

    /**
     *
     * @param Partner   $partner
     * @param \DateTime $start
     * @param \DateTime $end
     * @param array     $products
     *
     * @return ProductRate[]
     */
    public function findByDateRange(Partner $partner, \DateTime $start, \DateTime $end, array $products = [])
    {
        $queryBuilder = $this
            ->createQueryBuilder('product_rate')
            ->where('product_rate.date >= :start')
            ->andWhere('product_rate.date <= :end')
            ->andWhere('product_rate.partner = :partner')
            ->setParameter('start', $start->setTime(0, 0, 0))
            ->setParameter('end', $end->setTime(0, 0, 0))
            ->setParameter('partner', $partner);

        if (sizeof($products) > 0) {
            $queryBuilder
                ->andWhere('product_rate.product IN (:products)')
                ->setParameter('products', $products);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     *
     * @param Product $product
     *
     * @return void
     */
    public function updatePartner(Product $product)
    {
        $query = $this->createQueryBuilder('p');
        $query
            ->update()
            ->set('p.partner', ':partner')
            ->where('p.product = :product')
            ->setParameter('partner', $product->getPartner())
            ->setParameter('product', $product);

        $query->getQuery()->execute();
    }

    /**
     *
     * @param Product $product
     *
     * @return void
     */
    public function reset(Product $product)
    {
        $this
            ->createQueryBuilder('r')
            ->update()
            ->set('r.amount', 0)
            ->set('r.updatedAt', ':now')
            ->where('r.date >= :now')
            ->andWhere('r.product = :product')
            ->setParameter('product', $product)
            ->setParameter('now', date_create())
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
        $sql = 'SELECT date, amount as quantity from product_rate WHERE product_id = ? AND date >= ? AND date <= ? AND amount > 0 ORDER BY date ASC';
        $stmt = $this->_em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $product->getId(), ParameterType::INTEGER);
        $stmt->bindValue(2, $startDate->format('Y-m-d'));
        $stmt->bindValue(3, $endDate->format('Y-m-d'));
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_FUNC, function ($date, $quantity) {
            return [
                'date' => $date,
                'quantity' => floatval($quantity),
            ];
        });
    }
}
