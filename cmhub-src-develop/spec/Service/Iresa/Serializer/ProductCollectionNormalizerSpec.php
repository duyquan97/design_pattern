<?php

namespace spec\App\Service\Iresa\Serializer;

use App\Entity\Factory\ProductFactory;
use App\Entity\Partner;
use App\Entity\Product;
use App\Model\Factory\ProductCollectionFactory;
use App\Model\ProductCollection;
use App\Service\Iresa\Serializer\ProductCollectionNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use App\Model\PartnerInterface;
use App\Repository\ProductRepository;

class   ProductCollectionNormalizerSpec extends ObjectBehavior
{

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductCollectionNormalizer::class);
    }

    /**
     * @param EntityManagerInterface|\PhpSpec\Wrapper\Collaborator $entityManager
     * @param ProductFactory|\PhpSpec\Wrapper\Collaborator $productFactory
     * @param ProductCollectionFactory|\PhpSpec\Wrapper\Collaborator $productCollectionFactory
     */
    function let(EntityManagerInterface $entityManager, ProductFactory $productFactory, ProductCollectionFactory $productCollectionFactory)
    {
        $this->beConstructedWith($entityManager, $productFactory, $productCollectionFactory);
    }

    function it_denormalize(ProductCollectionFactory $productCollectionFactory,Partner $partner,EntityManagerInterface $entityManager,ProductRepository $productRepository,Product $product,Product $product2,ProductCollection $productCollection, ProductFactory $productFactory)
    {

        $productCollectionFactory->create(Argument::any())->shouldBeCalledOnce()->willReturn($productCollection);

        $entityManager->getRepository(Product::class)->willReturn($productRepository);
        $productRepository->findOneBy(['identifier' => '123'])->willReturn($product);
        $productRepository->findOneBy(['identifier' => '124'])->willReturn(null);
        $productFactory->create()->willReturn($product2);
        $product->setName('New Name 1')->shouldNotBeCalled()->willReturn($product);
        $product->setPartner($partner)->shouldBeCalled()->willReturn($product);
        $product2->setIdentifier(Argument::any())->willReturn($product2);
        $product2->setName('New Name 2')->shouldBeCalled()->willReturn($product2);
        $product2->setDescription('New Name 2')->willReturn($product2);
        $product2->setPartner($partner)->willReturn($product2)->shouldBeCalled();
        $productCollection->addProduct($product)->shouldBeCalled()->willReturn($productCollection);
        $productCollection->addProduct($product2)->shouldBeCalled()->willReturn($productCollection);

        $this->denormalize($this->getIresaMock(),['partner'=>$partner])->shouldBe($productCollection);
    }


    private function getIresaMock(){

        $iresa1 = new \stdClass();
        $iresa1->roomTypeCode = '123';
        $iresa1->roomName= 'New Name 1';
        $iresa2 = new \stdClass();
        $iresa2->roomTypeCode = '124';
        $iresa2->roomName= 'New Name 2';

        $mockData[] = $iresa1;
        $mockData[] = $iresa2;
        return $mockData;
    }
}
