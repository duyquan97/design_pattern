<?php

namespace App\Tests\Service\EAI;

use App\Entity\Partner;
use App\Entity\Product;
use App\Model\Availability;
use App\Model\ProductAvailability;
use App\Model\ProductAvailabilityCollection;
use App\Model\ProductRate;
use App\Model\ProductRateCollection;
use App\Model\Rate;
use App\Service\EAI\EAIProcessor;
use App\Tests\BaseWebTestCase;

/**
 * Class EAIProcessorTest
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class EAIProcessorTest extends BaseWebTestCase
{
    /**
     * @var EAIProcessor
     */
    private $eaiProcessor;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::runConsoleCommand('hautelook:fixtures:load --no-interaction --quiet');
    }

    public function setUp()
    {
        self::bootKernel();
        $this->eaiProcessor = $this->getContainer()->get(EAIProcessor::class);

        parent::setUp();
    }

    public function testAvailabilitiesUpdate()
    {
        /** @var Partner $partner */
        $partner = $this->getPartner('00019091');

        /** @var Product $product */
        $product = $this->getProduct('235854');
        /** @var Product $product1 */
        $product1 = $this->getProduct('393333');

        $stock = rand(1, 12);
        $availability = (new Availability($product))
            ->setStart($this->getDate('2019-09-01'))
            ->setEnd($this->getDate('2019-09-15'))
            ->setStock($stock)
            ->setStopSale(false);

        $stock1 = rand(1, 12);
        $availability1 = (new Availability($product1))
            ->setStart($this->getDate('2019-09-05'))
            ->setEnd($this->getDate('2019-09-10'))
            ->setStock($stock1)
            ->setStopSale(false);

        $productAvailability = (new ProductAvailability($product))
            ->setPartner($partner)
            ->setAvailabilities([$availability]);

        $productAvailability1 = (new ProductAvailability($product1))
            ->setPartner($partner)
            ->setAvailabilities([$availability1]);

        $productAvailabilityCollection = (new ProductAvailabilityCollection($partner))
            ->addProductAvailability($productAvailability);
        $productAvailabilityCollection->addProductAvailability($productAvailability1);

        $response = $this->eaiProcessor->updateAvailabilities($productAvailabilityCollection);

        $this->assertEquals(202, $response->getStatusCode());
        $this->assertIsString($response->getTransactionId());
    }

    public function testProductRatesUpdate()
    {
        /** @var Partner $partner */
        $partner = $this->getPartner('00019091');
        /** @var Product $product */
        $product = $this->getProduct('235854');
        /** @var Product $product1 */
        $product1 = $this->getProduct('393333');

        $rate = (new Rate())
            ->setProduct($product)
            ->setStart($this->getDate('2019-09-01'))
            ->setEnd($this->getDate('2019-09-05'))
            ->setAmount(rand(1, 12));

        $rate1 = (new Rate())
            ->setProduct($product)
            ->setStart($this->getDate('2019-09-05'))
            ->setEnd($this->getDate('2019-09-15'))
            ->setAmount(rand(1, 12));

        $productRate = (new ProductRate($product))
            ->setRates([
                $rate,
                $rate1
            ]);

        $productRateCollection = (new ProductRateCollection($partner))
            ->addProductRate($productRate);

        $rate2 = (new Rate())
            ->setProduct($product1)
            ->setStart($this->getDate('2019-09-05'))
            ->setEnd($this->getDate('2019-09-09'))
            ->setAmount(rand(1, 12));

        $rate3 = (new Rate())
            ->setProduct($product1)
            ->setStart($this->getDate('2019-09-10'))
            ->setEnd($this->getDate('2019-09-12'))
            ->setAmount(rand(1, 12));

        $productRate1 = (new ProductRate($product1))
            ->setRates([
                $rate2,
                $rate3
            ]);

        $productRateCollection->addProductRate($productRate1);

        $response = $this->eaiProcessor->updateRates($productRateCollection);

        $this->assertEquals(202, $response->getStatusCode());
        $this->assertIsString($response->getTransactionId());
    }


    private function getPartner(String $partner)
    {
        return $this->getRepository(Partner::class)->findOneByIdentifier($partner);
    }

    private function getProduct(String $product)
    {
        return $this->getRepository(Product::class)->findOneBy(['identifier' => $product]);
    }

    private function getDate(String $date)
    {
        return new \DateTime($date);
    }
}
