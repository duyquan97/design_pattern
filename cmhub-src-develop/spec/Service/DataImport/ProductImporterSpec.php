<?php

namespace spec\App\Service\DataImport;

use App\Entity\ImportData;
use App\Entity\Partner;
use App\Entity\Product;
use App\Exception\PartnerNotFoundException;
use App\Exception\ProductNotFoundException;
use App\Exception\ValidationException;
use App\Repository\PartnerRepository;
use App\Model\ImportDataType;
use App\Service\DataImport\Model\Factory\ProductRowCollectionFactory;
use App\Service\DataImport\Model\Factory\ProductRowFactory;
use App\Service\DataImport\Model\ProductRow;
use App\Service\DataImport\Model\ProductRowCollection;
use App\Service\DataImport\ProductImporter;
use App\Service\Loader\PartnerLoader;
use App\Service\Loader\ProductLoader;
use App\Utils\CsvReader;
use Doctrine\Common\Collections\ArrayCollection;
use League\Csv\Reader;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class ExperienceImporterSpec
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductImporterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductImporter::class);
    }

    function let(PartnerRepository $partnerRepository, ProductLoader $productLoader, ProductRowFactory $productRowFactory,CsvReader $csvReader,ProductRowCollectionFactory $productRowCollectionFactory)
    {
        $this->beConstructedWith($partnerRepository, $productLoader,  $productRowFactory, $csvReader,$productRowCollectionFactory);
    }

    function it_support_experience_type(ImportData $importData)
    {
        $importData->getType()->willReturn(ImportDataType::CHAINING_ROOM);
        $this->supports($importData)->shouldReturn(true);
    }

    function it_does_not_support_other_type(ImportData $importData)
    {
        $importData->getType()->willReturn(ImportDataType::AVAILABILITY);
        $this->supports($importData)->shouldReturn(false);
    }

    function it_import_row(Product $product, ProductRow $productRow,PartnerRepository $partnerRepository, Partner $partner, ProductRowFactory $productRowFactory,ProductLoader $productLoader)
    {
        $productRowFactory->create()->willReturn($productRow);

        $partnerRepository->findOneBy(['identifier' => '00019091'])->willReturn($partner);
        $productRow->setPartner($partner)->willReturn($productRow);

        $productLoader->find($partner,'393333', [])->willReturn($product);
        $productLoader->find($partner, '', [])->willReturn(null);

        $product->setSellable(false)->shouldBeCalled();
        $product->getLinkedProducts()->willReturn(new ArrayCollection([]));
        $product->setName('Room test')->shouldBeCalled();

        $productRow->setProduct($product)->shouldBeCalled()->willReturn($productRow);

        $this->process(['00019091' , '393333','','Room test','0'])->shouldBe($productRow);
    }

    function it_import_row_no_data(ProductRowFactory $productRowFactory, ProductRow $productRow, ImportData $importData,CsvReader $csvReader,  ProductRowCollectionFactory $productRowCollectionFactory,ProductRowCollection $productRowCollection)
    {
        $request = ['00019091' , '393333','393333','Room test','0'];
        $productRowFactory->create()->willReturn($productRow);
        $productRow->setException(Argument::type(ValidationException::class))->shouldBeCalled()->willReturn($productRow);
        $this->process($request)->shouldBe($productRow);
    }

    function it_import_row_no_partner(ImportData $importData,CsvReader $csvReader, ProductRowCollectionFactory $productRowCollectionFactory,ProductRowCollection $productRowCollection, ProductRow $productRow,PartnerRepository $partnerRepository,  ProductRowFactory $productRowFactory)
    {
        $productRowFactory->create()->willReturn($productRow);
        $partnerRepository->findOneBy(['identifier' => '00019091'])->willReturn(null);
        $productRow->setException(Argument::type(PartnerNotFoundException::class))->shouldBeCalled();

        $this->process(['00019091' , '393333','393332','Room test','0'])->shouldBe($productRow);
    }

    function it_import_row_no_product(ImportData $importData,CsvReader $csvReader,  ProductRowCollectionFactory $productRowCollectionFactory,ProductRowCollection $productRowCollection, ProductRow $productRow,PartnerLoader $partnerLoader, Partner $partner, ProductRowFactory $productRowFactory,PartnerRepository $partnerRepository)
    {
        $productRowFactory->create()->willReturn($productRow);
        $partnerRepository->findOneBy(['identifier' => '00019091'])->willReturn($partner);
        $productRow->setPartner($partner)->willReturn($productRow);
        $partnerRepository->findOneBy(['identifier' => '393333'])->willReturn(null);
        $productRow->setException(Argument::type(ProductNotFoundException::class))->shouldBeCalled();

        $this->process(['00019091' , '393333','','Room test','0'])->shouldBe($productRow);
    }

    function it_import_row_master_product(ImportData $importData,CsvReader $csvReader, Product $product,Product $masterProduct, ProductRowCollectionFactory $productRowCollectionFactory,ProductRowCollection $productRowCollection, ProductRow $productRow,PartnerRepository $partnerRepository, Partner $partner, ProductRowFactory $productRowFactory,ProductLoader $productLoader)
    {
        $productRowFactory->create()->willReturn($productRow);

        $partnerRepository->findOneBy(['identifier' => '00019092'])->willReturn($partner);
        $productRow->setPartner($partner)->willReturn($productRow);

        $productLoader->find($partner,'393333', [])->willReturn($product);

        $productLoader->find($partner,'393334', [])->willReturn($masterProduct);

        $product->setMasterProduct($masterProduct)->shouldBeCalled()->willReturn($product);
        $masterProduct->setSellable(true)->willReturn($masterProduct)->shouldBeCalled();
        $masterProduct->setName('Room test')->willReturn($masterProduct)->shouldBeCalled();
        $product->setSellable(true)->shouldBeCalled()->willReturn($product);
        $product->getLinkedProducts()->willReturn(array($product))->shouldBeCalled();
        $productRow->setProduct($product)->shouldBeCalled()->willReturn($productRow);

        $this->process(['00019092','393333','393334','Room test','1'])->shouldBe($productRow);
    }

}