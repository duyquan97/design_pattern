<?php

namespace App\Service\DataImport;

use App\Entity\Availability;
use App\Entity\Factory\TransactionFactory;
use App\Entity\ImportData;
use App\Entity\Transaction;
use App\Entity\TransactionChannel;
use App\Entity\TransactionType;
use App\Exception\ComponentNotFoundException;
use App\Exception\DateFormatException;
use App\Exception\ValidationException;
use App\Model\ImportDataType;
use App\Repository\AvailabilityRepository;
use App\Service\DataImport\Model\AvailabilityRow;
use App\Service\Loader\ProductLoader;

/**
 * Class AvailabilityImporter
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class AvailabilityImporter implements DataImporterInterface
{
    private const MASTER_COMPONENT_ID_INDEX = 0;
    private const COMPONENT_NAME_INDEX = 1;
    private const DATE_INDEX = 2;
    private const AVAILABILITY_INDEX = 3;

    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     *
     * @var AvailabilityRepository
     */
    private $availabilityRepository;

    /**
     * @var ProductLoader
     */
    private $productLoader;

    /**
     * AvailabilityImporter constructor.
     *
     * @param TransactionFactory     $transactionFactory
     * @param AvailabilityRepository $availabilityRepository
     * @param ProductLoader          $productLoader
     */
    public function __construct(TransactionFactory $transactionFactory, AvailabilityRepository $availabilityRepository, ProductLoader $productLoader)
    {
        $this->transactionFactory = $transactionFactory;
        $this->availabilityRepository = $availabilityRepository;
        $this->productLoader = $productLoader;
    }

    /**
     * @param array $row
     *
     * @return AvailabilityRow
     */
    public function process(array $row): AvailabilityRow
    {
        $availabilityRow = new AvailabilityRow();

        try {
            if (!isset($row[self::MASTER_COMPONENT_ID_INDEX], $row[self::DATE_INDEX], $row[self::AVAILABILITY_INDEX])) {
                $availabilityRow->setException(new ValidationException(sprintf('Error on row %s', json_encode($row))));

                return $availabilityRow;
            }

            $masterProduct = $this->productLoader->getProductByIdentifier($row[self::MASTER_COMPONENT_ID_INDEX]);
            if (!$masterProduct) {
                throw new ComponentNotFoundException($row[self::MASTER_COMPONENT_ID_INDEX]);
            }

            $date = \DateTime::createFromFormat('d/m/Y', $row[self::DATE_INDEX]);
            if (!$date instanceof \DateTime) {
                throw new DateFormatException('d/m/Y');
            }

            $availabilityRow->setProductName($row[self::COMPONENT_NAME_INDEX] ?? null);

            $availability = $this
                ->availabilityRepository
                ->findOneBy(
                    [
                        'date'    => $date,
                        'product' => $masterProduct,
                    ]
                );

            if (!$availability) {
                $availability = new Availability();
            }

            $availability
                ->setProduct($masterProduct)
                ->setPartner($masterProduct->getPartner())
                ->setStock((int) $row[self::AVAILABILITY_INDEX]);

            $availability->setDate($date);

            $transaction = $this->createTransaction()->setPartner($masterProduct->getPartner());
            $availability->setTransaction($transaction);

            $availabilityRow->setAvailability($availability);
        } catch (\Exception $exception) {
            $availabilityRow->setException($exception);
        }

        return $availabilityRow;
    }

    /**
     * @param ImportData $importData
     *
     * @return bool
     */
    public function supports(ImportData $importData): bool
    {
        return ImportDataType::AVAILABILITY === $importData->getType();
    }

    /**
     *
     * @return Transaction
     *
     * @throws \Exception
     */
    private function createTransaction(): Transaction
    {
        return $this
            ->transactionFactory
            ->create(
                TransactionType::AVAILABILITY,
                TransactionChannel::IRESA
            );
    }
}
