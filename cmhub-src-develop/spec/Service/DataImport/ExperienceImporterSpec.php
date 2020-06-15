<?php

namespace spec\App\Service\DataImport;

use App\Entity\Experience;
use App\Entity\Factory\PartnerFactory;
use App\Entity\ImportData;
use App\Entity\Partner;
use App\Exception\ValidationException;
use App\Repository\ExperienceRepository;
use App\Repository\PartnerRepository;
use App\Model\ImportDataType;
use App\Service\DataImport\ExperienceImporter;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;

/**
 * Class ExperienceImporterSpec
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ExperienceImporterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ExperienceImporter::class);
    }

    function let(ExperienceRepository $experienceRepository, PartnerRepository $partnerRepository, PartnerFactory $partnerFactory, EntityManagerInterface $entityManager)
    {
        $this->beConstructedWith($experienceRepository, $partnerRepository, $partnerFactory, $entityManager);
    }

    function it_support_experience_type(ImportData $importData)
    {
        $importData->getType()->willReturn(ImportDataType::EXPERIENCE);
        $this->supports($importData)->shouldReturn(true);
    }

    function it_does_not_support_other_type(ImportData $importData)
    {
        $importData->getType()->willReturn(ImportDataType::AVAILABILITY);
        $this->supports($importData)->shouldReturn(false);
    }

    function it_import_row(PartnerRepository $partnerRepository, Partner $partner)
    {
        $partnerRepository->findOneBy(['identifier' => '00019091'])->willReturn($partner);
        $experienceRow = $this->process($this->data);
        $experienceRow->getException()->shouldBeNull();
        $experience = $experienceRow->getEntity();
        $experience->shouldHaveType(Experience::class);
        $experience->getName()->shouldBe('Experience');
        $experience->getIdentifier()->shouldBe('123456');
        $experience->getPrice()->shouldBeLike(123);
        $experience->getCommissionType()->shouldBe('amount');
        $experience->getCommission()->shouldBeLike(12);
    }

    function it_create_empty_partner(EntityManagerInterface $entityManager, PartnerRepository $partnerRepository, PartnerFactory $partnerFactory, Partner $partner)
    {
        $partnerRepository->findOneBy(['identifier' => '00019091'])->willReturn(null);
        $partnerFactory->create()->shouldBeCalled()->willReturn($partner);
        $partner->setIdentifier('00019091')->shouldBeCalled()->willReturn($partner);
        $entityManager->persist($partner)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();
        $experienceRow = $this->process($this->data);
        $experienceRow->getException()->shouldBeNull();
        $experience = $experienceRow->getEntity();
        $experience->shouldHaveType(Experience::class);
        $experience->getName()->shouldBe('Experience');
        $experience->getIdentifier()->shouldBe('123456');
        $experience->getPrice()->shouldBeLike(123);
        $experience->getCommissionType()->shouldBe('amount');
        $experience->getCommission()->shouldBeLike(12);
    }

    function it_import_row_fail_exception()
    {
        $experienceRow = $this->process([
            null,
            'Experience',
            123,
            '00019091',
            12,
            'amount']);
        $experienceRow->getException()->shouldBeAnInstanceOf(ValidationException::class);
    }

    protected $data = [
        '123456',
        'Experience',
        123,
        '00019091',
        12,
        'amount'
    ];
}