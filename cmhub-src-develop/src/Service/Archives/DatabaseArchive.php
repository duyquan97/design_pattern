<?php

namespace App\Service\Archives;

use App\Exception\ValidationException;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class DatabaseArchive
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class DatabaseArchive
{
    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * @var string $tableTarget
     */
    protected $tableTarget;

    /**
     * @var string $tableSource
     */
    protected $tableSource;

    /**
     * @var string $period
     */
    protected $period;

    /**
     * @var array $conditions
     */
    private $conditions;

    /**
     * DatabaseArchive constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param string                 $tableSource
     * @param string                 $tableTarget
     * @param array                  $conditions
     * @param string                 $period
     *
     */
    public function __construct(EntityManagerInterface $entityManager, string $tableSource, string $tableTarget, array $conditions = [], string $period = '-30 day')
    {
        $this->entityManager = $entityManager;
        $this->tableSource = $tableSource;
        $this->tableTarget = $tableTarget;
        $this->conditions = $conditions;
        $this->period = $period;
    }

    /**
     *
     * @return void
     *
     * @throws DBALException
     * @throws ValidationException
     */
    public function archive()
    {
        // Create archive table if doesn't exists
        $result = $this
            ->execute(
                sprintf(
                    $query = 'CREATE TABLE IF NOT EXISTS %s LIKE %s',
                    $this->tableTarget,
                    $this->tableSource
                )
            );

        if (!$result) {
            throw new \RuntimeException(sprintf('Failed to execute query `%s`', $query));
        }

        // Insert data to archive table
        $result = $this
            ->execute(
                sprintf(
                    'INSERT INTO %s SELECT * FROM %s WHERE %s',
                    $this->tableTarget,
                    $this->tableSource,
                    implode(' and ', $this->conditions)
                )
            );

        if (!$result) {
            throw new \RuntimeException(sprintf('Failed to execute query `%s`', $query));
        }

        // Delete archived data
        $result = $this
            ->execute(
                sprintf(
                    'DELETE FROM %s  WHERE id IN (SELECT id FROM %s WHERE %s)',
                    $this->tableSource,
                    $this->tableTarget,
                    implode(' and ', $this->conditions)
                )
            );

        if (!$result) {
            throw new \RuntimeException(sprintf('Failed to execute query `%s`', $query));
        }
    }

    /**
     *
     * @return string
     */
    public function getTableSource(): string
    {
        return $this->tableSource;
    }

    /**
     *
     * @param string $query
     *
     * @return bool
     *
     * @throws ValidationException
     * @throws DBALException
     */
    private function execute(string $query): bool
    {
        $conn = $this->entityManager->getConnection();
        $pastDate = date_create($this->period);
        $today = date_create();
        if ($pastDate >= $today) {
            throw new ValidationException('Only data in the past can be archived.');
        }

        $result = $conn->prepare($query);

        return $result->execute(['date' => $pastDate->format('Y-m-d')]);
    }
}
