<?php

namespace spec\App\Service\DataImport;

use App\Entity\Availability;
use App\Entity\Partner;
use App\Entity\Product;
use App\Entity\Transaction;
use App\Entity\TransactionChannel;
use App\Entity\TransactionType;
use App\Entity\Factory\TransactionFactory;
use App\Entity\ImportData;
use App\Exception\ComponentNotFoundException;
use App\Exception\DateFormatException;
use App\Exception\ValidationException;
use App\Model\ImportDataType;
use App\Repository\AvailabilityRepository;
use App\Service\DataImport\AvailabilityImporter;
use App\Service\DataImport\Model\AvailabilityRow;
use App\Service\Loader\ProductLoader;
use App\Utils\CsvReader;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class ExperienceImporterSpec
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class AvailabilityImporterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AvailabilityImporter::class);
    }

    function let(TransactionFactory $transactionFactory, AvailabilityRepository $availabilityRepository, ProductLoader $productLoader, CsvReader $csvReader)
    {
        $this->beConstructedWith($transactionFactory, $availabilityRepository,  $productLoader, $csvReader);
    }

    function it_support_experience_type(ImportData $importData)
    {
        $importData->getType()->willReturn(ImportDataType::AVAILABILITY);
        $this->supports($importData)->shouldReturn(true);
    }

    function it_does_not_support_other_type(ImportData $importData)
    {
        $importData->getType()->willReturn(ImportDataType::CHAINING_ROOM);
        $this->supports($importData)->shouldReturn(false);
    }

    function it_import_row
    (
        ProductLoader $productLoader,
        Product $product,
        AvailabilityRow $availabilityRow,
        Availability $availability,
        Partner $partner,
        TransactionFactory $transactionFactory,
        EntityManagerInterface $entityManager,
        ObjectRepository $objectRepository,
        Transaction $transaction
    )
    {
        $date = \DateTime::createFromFormat('d/m/Y', '20/12/2019');
        $productLoader->getProductByIdentifier('110224')->willReturn($product);
        $availabilityRow->setProductName('Product 1')->willReturn($availabilityRow);
        $entityManager->getRepository(Availability::class)->willReturn($objectRepository);
        $objectRepository->findOneBy(['date'=>$date, 'product'=>$product])->willReturn($availability);
        $availability->setProduct($product)->willReturn($availability);
        $product->getPartner()->willReturn($partner);
        $availability->setPartner($partner)->willReturn($availability);
        $availability->setStock(10)->willReturn($availability);
        $availability->setDate($date)->willReturn($availability);
        $transactionFactory->create(TransactionType::AVAILABILITY, TransactionChannel::IRESA)->willReturn($transaction);
        $transaction->setPartner($partner)->willReturn($transaction);
        $availability->setTransaction($transaction)->willReturn($availability);
        $availabilityRow->setAvailability($availability)->willReturn($availabilityRow);

        $availabilityRowResult = $this->process(['110224', 'Product 1', '20/12/2019', '10']);
        $availabilityRowResult->getException()->shouldBeNull();
        $availabilityRowObject = $availabilityRowResult->getEntity();
        $availabilityRowObject->shouldHaveType(Availability::class);
        $availabilityRowObject->getStock()->shouldBeLike(10);
    }

    function it_import_row_fail_exception()
    {
        $availabilityRow = $this->process([null, 'Product 1', '20/12/2019', '']);
        $availabilityRow->getException()->shouldBeAnInstanceOf(ValidationException::class);
    }
}