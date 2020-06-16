<?php
// src/Message/SmsNotification.php
namespace App\Message;

use App\Entity\Product;

class SmsNotification
{
    private $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function getContent()
    {
        return $this->product;
    }
}
