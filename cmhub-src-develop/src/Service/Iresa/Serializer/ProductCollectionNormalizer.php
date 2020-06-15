<?php

namespace App\Service\Iresa\Serializer;

use App\Entity\Factory\ProductFactory;
use App\Entity\Product;
use App\Model\Factory\ProductCollectionFactory;
use App\Model\ProductCollection;
use App\Service\Serializer\NormalizerInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class ProductCollectionNormalizer
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductCollectionNormalizer implements NormalizerInterface
{
    /**
     *
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     *
     * @var ProductFactory
     */
    private $productFactory;


    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * ProductCollectionNormalizer constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ProductFactory $productFactory
     * @param ProductCollectionFactory $productCollectionFactory
     */
    public function __construct(EntityManagerInterface $entityManager, ProductFactory $productFactory, ProductCollectionFactory $productCollectionFactory)
    {
        $this->entityManager = $entityManager;
        $this->productFactory = $productFactory;
        $this->productCollectionFactory = $productCollectionFactory;
    }


    /**
     *
     * @param mixed $object
     * @param array $context
     *
     * @return mixed
     */
    public function normalize($object, array $context = array())
    {
        // TODO: Implement normalize() method.
    }

    /**
     *
     * @param mixed $data
     * @param array $context
     *
     * @return mixed
     */
    public function denormalize($data, array $context = array())
    {
        $collection = $this->productCollectionFactory->create($context['partner']);
        foreach ($data as $iresaProduct) {
            $product = $this->entityManager->getRepository(Product::class)->findOneBy(['identifier' => $iresaProduct->roomTypeCode]);
            if (!$product) {
                $collection->addProduct(
                    $this
                        ->productFactory
                        ->create()
                        ->setIdentifier($iresaProduct->roomTypeCode)
                        ->setName($iresaProduct->roomName)
                        ->setDescription($iresaProduct->roomName)
                        ->setPartner($context['partner'])
                );

                continue;
            }

            $product->setPartner($context['partner']);
            $collection->addProduct($product);
        }

        return $collection;
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsNormalization(string $class): bool
    {
        return false;
    }

    /**
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsDenormalization(string $class): bool
    {
        return ProductCollection::class;
    }
}
