<?php

namespace App\Service\DataImport;

use App\Entity\ImportData;
use App\Entity\Product;
use App\Exception\PartnerNotFoundException;
use App\Exception\ProductNotFoundException;
use App\Exception\ValidationException;
use App\Model\ImportDataType;
use App\Repository\PartnerRepository;
use App\Service\DataImport\Model\Factory\ProductRowFactory;
use App\Service\DataImport\Model\ProductRow;
use App\Service\Loader\ProductLoader;

/**
 * Class ChainingFileImporter
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 *
 */
class ProductImporter implements DataImporterInterface
{
    private const PARTNER_INDEX = 0;
    private const ROOM_COMPONENT_INDEX = 1;
    private const MASTER_ROOM_COMPONENT_INDEX = 2;
    private const COMPONENT_NAME_INDEX = 3;
    private const COMPONENT_SELLABLE = 4;

    /**
     * @var PartnerRepository
     */
    private $partnerRepository;

    /**
     * @var ProductLoader
     */
    private $productLoader;

    /**
     * @var ProductRowFactory
     */
    private $productRowFactory;

    /**
     * ProductImporter constructor.
     *
     * @param PartnerRepository $partnerRepository
     * @param ProductLoader $productLoader
     * @param ProductRowFactory $productRowFactory
     */
    public function __construct(PartnerRepository $partnerRepository, ProductLoader $productLoader, ProductRowFactory $productRowFactory)
    {
        $this->partnerRepository = $partnerRepository;
        $this->productLoader = $productLoader;
        $this->productRowFactory = $productRowFactory;
    }

    /**
     * @param ImportData $importData
     *
     * @return bool
     */
    public function supports(ImportData $importData): bool
    {
        return ImportDataType::CHAINING_ROOM === $importData->getType();
    }

    /**
     * @param array $row
     *
     * @return ProductRow
     */
    public function process(array $row): ?ProductRow
    {
        $productRow = $this->productRowFactory->create();

        try {
            if (isset($row[self::ROOM_COMPONENT_INDEX], $row[self::MASTER_ROOM_COMPONENT_INDEX]) &&
                trim($row[self::ROOM_COMPONENT_INDEX]) === trim($row[self::MASTER_ROOM_COMPONENT_INDEX])
            ) {
                $productRow->setException(new ValidationException(sprintf('Error on row %s', json_encode($row))));

                return $productRow;
            }

            $partner = $this->partnerRepository->findOneBy(
                [
                    'identifier' => $row[self::PARTNER_INDEX],
                ]
            );

            if (!$partner) {
                throw new PartnerNotFoundException($row[self::PARTNER_INDEX]);
            }

            $productRow->setPartner($partner);

            /** @var Product $product */
            $product = $this->productLoader->find($partner, $row[self::ROOM_COMPONENT_INDEX], []);
            if (!$product) {
                throw new ProductNotFoundException($partner, $row[self::ROOM_COMPONENT_INDEX]);
            }

            $componentName = $row[self::COMPONENT_NAME_INDEX];
            $componentSellable = (bool) $row[self::COMPONENT_SELLABLE];

            /** @var Product $masterProduct */
            $masterProduct = $this->productLoader->find($partner, $row[self::MASTER_ROOM_COMPONENT_INDEX], []);
            if ($masterProduct) {
                $product->setMasterProduct($masterProduct);
                $masterProduct->setSellable($componentSellable);

                if ($componentName) {
                    $masterProduct->setName($componentName);
                }
            }

            $product->setSellable($componentSellable);

            foreach ($product->getLinkedProducts() as $child) {
                $child->setSellable($componentSellable);
            }

            if ($componentName && !$masterProduct) {
                $product->setName($componentName);
            }

            return $productRow->setProduct($product);
        } catch (\Exception $exception) {
            $productRow->setException($exception);
        }

        return $productRow;
    }
}
