<?php

namespace App\Service\Loader;

use App\Entity\ChannelManager;
use App\Entity\Partner;
use App\Model\PartnerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Class PartnerLoader
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class PartnerLoader
{
    /**
     *
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * PartnerLoader constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     *
     * @param string $identifier
     *
     * @return Partner|null
     */
    public function find(?string $identifier): ?PartnerInterface
    {
        /* @var Partner $partner */
        $partner = $this->getRepository()->findOneBy(
            [
                'identifier' => $identifier,
                'status'     => 'partner',
                'enabled'    => true,
            ]
        );

        if (!$partner) {
            return null;
        }

        return $partner;
    }

    /**
     * @param array $identifiers
     *
     * @return array
     *
     * @throws \Exception
     */
    public function findByIds(array $identifiers): array
    {
        $partners = $this->getRepository()->findBy([
            'identifier' => $identifiers,
            'enabled'    => true,
        ]);

        return $partners;
    }

    /**
     * @param ChannelManager $channelManager
     *
     * @return array
     *
     * @throws \Exception
     */
    public function findByChannelManager(ChannelManager $channelManager): array
    {
        $partners = $this->getRepository()->findBy([
            'channelManager' => $channelManager,
            'enabled'    => true,
        ]);

        return $partners;
    }

    /**
     *
     * @return EntityRepository
     */
    private function getRepository(): EntityRepository
    {
        return $this->entityManager->getRepository(Partner::class);
    }
}
