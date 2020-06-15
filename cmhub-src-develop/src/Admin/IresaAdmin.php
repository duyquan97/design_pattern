<?php

namespace App\Admin;

use App\Form\GetAvailabilitiesType;
use App\Model\ImportDataType;
use App\Repository\PartnerRepository;
use App\Service\HubEngine\CmHubBookingEngine;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class IresaAdmin
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class IresaAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'admin_app';
    protected $baseRoutePattern = 'iresa';
}
