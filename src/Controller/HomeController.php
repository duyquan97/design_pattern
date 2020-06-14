<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use Carbon\Carbon;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\FOSRestBundle;
use FOS\RestBundle\View\View;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;

class HomeController extends AbstractFOSRestController
{

    /**
     * Creates an Article resource
     * @Rest\Get("/api/home")
     * @return View
     */
    public function index(Request $request):View
    {
        $product = new Product();
        $form=$this->createForm(ProductType::class,$product);
        $data= json_decode($request->request->all(),true);
        $form->submit($data);
        if($form->isSubmitted()&&$form->isValid()){$em=$this->getDoctrine()->getManager();
        $em->persist($data);
        $em->flush();
        return $this->handleView($this->view(['status'=>'ok'],Response::HTTP_CREATED));
        }
        return $this->handleView($this->view($form->getErrors()));
//        return View::create($a, Response::HTTP_CREATED);
    }
}
