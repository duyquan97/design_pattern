<?php


namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class DefaultController
{
    /**
     *
     * @Route("/")
     *
     * @return Response
     */
    public function indexAction()
    {
        return new Response();
    }
}
