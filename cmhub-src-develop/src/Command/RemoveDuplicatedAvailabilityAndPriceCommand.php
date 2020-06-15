<?php

namespace App\Command;

use App\Entity\Availability;
use App\Entity\Partner;
use App\Entity\ProductRate;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RemoveDuplicatedAvailabilityAndPriceCommand
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class RemoveDuplicatedAvailabilityAndPriceCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * RemoveDuplicatedAvailabilityAndPriceCommand constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('cmhub:duplication:remove')
            ->setDescription('This command will remove any duplicated availability/price in the database');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->removeDuplicatedAvailability();
        $this->removeDuplicatedPrice();

        $this->entityManager->flush();
    }

    /**
     * @return void
     */
    private function removeDuplicatedAvailability(): void
    {
        $rsm = new ResultSetMappingBuilder($this->entityManager, ResultSetMappingBuilder::COLUMN_RENAMING_INCREMENT);
        $rsm->addRootEntityFromClassMetadata(Availability::class, 'a');
        $rsm->addJoinedEntityFromClassMetadata(Partner::class, 'n', 'a', 'partner', array('id' => 'partner_id'));
        $rsm->addFieldResult('a', 'id', 'id');
        $rsm->addFieldResult('a', 'date', 'date');
        $rsm->addFieldResult('a', 'stock', 'stock');
        $rsm->addFieldResult('a', 'stop_sale', 'stopSale');
        $rsm->addFieldResult('a', 'created_at', 'createdAt');
        $rsm->addFieldResult('a', 'updated_at', 'updatedAt');

        $sql = 'SELECT a.* FROM availability AS a INNER JOIN (SELECT MAX(id) as id, count(id) AS c FROM availability GROUP BY product_id, date HAVING c > 1) AS d ON a.id = d.id';
        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        $availabilities = $query->getResult();

        foreach ($availabilities as $availability) {
            $this->entityManager->remove($availability);
        }
    }

    /**
     * @return void
     */
    private function removeDuplicatedPrice(): void
    {
        $rsm = new ResultSetMappingBuilder($this->entityManager, ResultSetMappingBuilder::COLUMN_RENAMING_INCREMENT);
        $rsm->addRootEntityFromClassMetadata(ProductRate::class, 'r');
        $rsm->addJoinedEntityFromClassMetadata(Partner::class, 'n', 'r', 'partner', array('id' => 'partner_id'));
        $rsm->addFieldResult('r', 'id', 'id');
        $rsm->addFieldResult('r', 'date', 'date');
        $rsm->addFieldResult('r', 'amount', 'amount');
        $rsm->addFieldResult('r', 'created_at', 'createdAt');
        $rsm->addFieldResult('r', 'updated_at', 'updatedAt');

        $sql = 'SELECT r.* FROM product_rate AS r INNER JOIN (SELECT MAX(id) as id, count(id) AS c FROM product_rate GROUP BY product_id, date HAVING c > 1) AS d ON r.id = d.id';
        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        $rates = $query->getResult();

        foreach ($rates as $rate) {
            $this->entityManager->remove($rate);
        }
    }
}
