<?php

namespace App\Service\Loader;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Model\PartnerInterface;
use App\Model\ProductCollection;
use App\Model\ProductInterface;

/**
 * Class ProductLoader
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductLoader
{
    /**
     * @var ProductRepository
     */
    private $repository;

    /**
     * ProductLoader constructor.
     *
     * @param ProductRepository $repository
     */
    public function __construct(ProductRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     *
     * @param PartnerInterface $partner
     * @param null|string      $roomTypeCode
     * @param array            $criteria
     *
     * @return ProductInterface|null
     */
    public function find(PartnerInterface $partner, ?string $roomTypeCode, array $criteria = ['masterProduct' => null]): ?ProductInterface
    {
        $criteria = array_merge(
            [
                'partner'    => $partner,
                'identifier' => $roomTypeCode,
                'reservable' => true,
            ],
            $criteria
        );

        return $this
            ->repository
            ->findOneBy(
                $criteria
            );
    }

    /**
     *
     * @param PartnerInterface  $partner
     * @param array $criteria
     *
     * @return ProductCollection
     */
    public function getByPartner(PartnerInterface $partner, array $criteria = null): ProductCollection
    {

        if (null === $criteria) {
            $criteria = [
                'partner'       => $partner,
                'reservable'    => true,
                'masterProduct' => null,
            ];
        }

        return new ProductCollection(
            $partner,
            $this
                ->repository
                ->findBy($criteria)
        );
    }

    /**
     *
     * @param PartnerInterface $partner
     * @param array            $roomTypeCodes
     *
     * @return Product[]
     */
    public function getProductsByRoomCode(PartnerInterface $partner, array $roomTypeCodes): array
    {
        // TODO: Return ProductCollection instead of native array
        $filters = [
            'identifier' => $roomTypeCodes,
            'reservable' => true,
        ];

        if ($partner) {
            $filters['partner'] = $partner;
        }

        return $this
            ->repository
            ->findBy($filters);
    }

    /**
     * @param string $identifier
     *
     * @return ProductInterface|null
     */
    public function getProductByIdentifier(string $identifier): ?ProductInterface
    {
        return $this
            ->repository
            ->findOneBy(
                [
                    'identifier' => $identifier,
                ]
            );
    }

    /**
     *
     * @param \DateTime        $updatedDate
     * @param array            $partnerIds
     *
     * @return ProductCollection
     */
    public function getByUpdatedDate(\DateTime $updatedDate = null, array $partnerIds = null): ProductCollection
    {
        $queryBuilder = $this->repository->createQueryBuilder('product');
        if ($updatedDate) {
            $queryBuilder
                ->andWhere('product.updatedAt >= :dateFrom')
                ->setParameter('dateFrom', $updatedDate->format('Y-m-d H:i:s'));
        }

        if ($partnerIds) {
            $queryBuilder
                ->andWhere('product.partner IN (:partnerIds)')
                ->setParameter('partnerIds', $partnerIds);
        }

        return new ProductCollection(null, $queryBuilder->getQuery()->getResult());
    }
}
