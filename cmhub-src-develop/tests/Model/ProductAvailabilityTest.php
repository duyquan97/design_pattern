<?php

namespace App\Tests\Model;

use App\Entity\Product;
use App\Model\Availability;
use App\Model\ProductAvailability;
use PHPUnit\Framework\TestCase;

/**
 * Class ProductAvailabilityTest
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductAvailabilityTest extends TestCase
{
    /**
     * @test
     *
     * @covers \App\Model\ProductAvailability::addAvailability()
     */
    public function testAddAvailability()
    {
        $product = $this->createMock(Product::class);

        $availability1 = new Availability($product);
        $availability1->setStart(new \DateTime('2019-04-28'));
        $availability1->setEnd(new \DateTime('2019-05-05'));
        $availability1->setStock(1);

        $availability2 = new Availability($product);
        $availability2->setStart(new \DateTime('2019-05-01'));
        $availability2->setEnd(new \DateTime('2019-05-03'));
        $availability2->setStock(10);

        $productAvailability = new ProductAvailability($product);
        $productAvailability->addAvailability($availability1);
        $productAvailability->addAvailability($availability2);

        $this->assertEquals(8, count($productAvailability->getAvailabilities()));
        foreach ($productAvailability->getAvailabilities() as $availability) {
            if (
                $availability->getStart() < new \DateTime('2019-05-01') ||
                $availability->getStart() > new \DateTime('2019-05-03')
            ) {
                $this->assertEquals(1, $availability->getStock());
            } else {
                $this->assertEquals(10, $availability->getStock());
            }
        }
    }
}
