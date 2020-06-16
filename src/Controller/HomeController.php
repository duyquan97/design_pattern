<?php

namespace App\Controller;

use App\Entity\Product;
use App\Message\SmsNotification;
use App\Service\HelloService;
use FOS\RestBundle\View\View;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;


class HomeController  extends AbstractController
{

    /**
     * @Rest\Route("/message", methods={"GET"})
     * @param MessageBusInterface $bus
     */
    public function index(MessageBusInterface $bus)
    {

        $product = new Product();
        $product->setName('Quan');
       $sms = $this->dispatchMessage(new SmsNotification($product));
        dd($sms);
    }

    /**
     * @Route("/soap")
     */
    public function soap(HelloService $helloService)
    {
        $soapServer = new \SoapServer('wsdl.wsdl');
        $soapServer->setObject($helloService);
        $response = new Response();
        $response->headers->set('Content-Type', 'text/xml; charset=ISO-8859-1');

        ob_start();
        $soapServer->handle();
        $response->setContent(ob_get_clean());

        return $response;
    }
}
