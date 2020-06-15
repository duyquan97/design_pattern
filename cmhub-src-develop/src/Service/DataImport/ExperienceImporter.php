<?php

namespace App\Service\DataImport;

use App\Entity\Experience;
use App\Entity\Factory\PartnerFactory;
use App\Entity\ImportData;
use App\Exception\ValidationException;
use App\Model\CommissionType;
use App\Model\ImportDataType;
use App\Repository\ExperienceRepository;
use App\Repository\PartnerRepository;
use App\Service\DataImport\Model\ExperienceRow;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class ExperienceImporter
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ExperienceImporter implements DataImporterInterface
{
    private const EXPERIENCE_ID_INDEX = 0;
    private const EXPERIENCE_NAME_INDEX = 1;
    private const EXPERIENCE_PRICE_INDEX = 2;
    private const PARTNER_ID_INDEX = 3;
    private const COMMISSION_INDEX = 4;
    private const COMMISSION_TYPE_INDEX = 5;

    /**
     * @var PartnerRepository
     */
    private $partnerRepository;

    /**
     * @var PartnerFactory
     */
    private $partnerFactory;

    /**
     * @var ExperienceRepository
     */
    private $experienceRepository;

    /**
     *
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * ExperienceImporter constructor.
     *
     * @param ExperienceRepository   $experienceRepository
     * @param PartnerRepository      $partnerRepository
     * @param PartnerFactory         $partnerFactory
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ExperienceRepository $experienceRepository, PartnerRepository $partnerRepository, PartnerFactory $partnerFactory, EntityManagerInterface $entityManager)
    {
        $this->partnerRepository = $partnerRepository;
        $this->partnerFactory = $partnerFactory;
        $this->experienceRepository = $experienceRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param array $row
     *
     * @return ExperienceRow
     */
    public function process(array $row): ExperienceRow
    {
        $experienceRow = new ExperienceRow();

        try {
            if (!isset($row[self::EXPERIENCE_ID_INDEX], $row[self::EXPERIENCE_NAME_INDEX], $row[self::EXPERIENCE_PRICE_INDEX], $row[self::PARTNER_ID_INDEX])) {
                $experienceRow->setException(new ValidationException(sprintf('Error on row %s', json_encode($row))));

                return $experienceRow;
            }

            $partner = $this->partnerRepository->findOneBy(['identifier' => trim($row[self::PARTNER_ID_INDEX])]);
            if (!$partner) {
                $partner = $this->partnerFactory->create();
                $partner->setIdentifier(trim($row[self::PARTNER_ID_INDEX]));
                $this->entityManager->persist($partner);
                $this->entityManager->flush();
            }

            $experience = $this
                ->experienceRepository
                ->findOneBy(
                    [
                        'identifier' => trim($row[self::EXPERIENCE_ID_INDEX]),
                    ]
                );

            if (!$experience) {
                $experience = new Experience();
                $experience->setIdentifier(trim($row[self::EXPERIENCE_ID_INDEX]));
            }

            $experience
                ->setPartner($partner)
                ->setName(utf8_encode(trim($row[self::EXPERIENCE_NAME_INDEX])))
                ->setPrice((float) trim($row[self::EXPERIENCE_PRICE_INDEX]));

            $commission = 0;
            if (isset($row[self::COMMISSION_INDEX])) {
                $commission = (float) trim($row[self::COMMISSION_INDEX]);
            }
            $experience->setCommission($commission);

            $commissionType = CommissionType::AMOUNT;
            if (isset($row[self::COMMISSION_TYPE_INDEX]) && in_array(trim($row[self::COMMISSION_TYPE_INDEX]), CommissionType::ALL)) {
                $commissionType = trim($row[self::COMMISSION_TYPE_INDEX]);
            }
            $experience->setCommissionType($commissionType);


            $experienceRow->setExperience($experience);
        } catch (\Exception $exception) {
            $experienceRow->setException($exception);
        }

        return $experienceRow;
    }

    /**
     * @param ImportData $importData
     *
     * @return bool
     */
    public function supports(ImportData $importData): bool
    {
        return ImportDataType::EXPERIENCE === $importData->getType();
    }
}
