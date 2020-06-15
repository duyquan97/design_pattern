<?php

namespace App\Admin;

use App\Entity\Guest;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

/**
 * Class BookingProductAdmin
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingProductAdmin extends AbstractAdmin
{
    /**
     *
     * @param DatagridMapper $datagridMapper
     *
     * @return void
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('totalAmount')
            ->add('currency')
            ->add('createdAt');
    }

    /**
     *
     * @param RouteCollection $collection
     *
     * @return void
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('edit');
    }

    /**
     *
     * @param ListMapper $listMapper
     *
     * @return void
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('totalAmount')
            ->add('currency')
            ->add('product')
            ->add('guests')
            ->add(
                '_action',
                null,
                [
                    'actions' =>
                        [
                            'show'   => [],
                            'delete' => [],
                        ],
                ]
            );
    }

    /**
     *
     * @param FormMapper $formMapper
     *
     * @return void
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $entityManager = $this->getDatagrid()->getQuery()->getEntityManager(Guest::class);
        $query = $entityManager->createQueryBuilder('g')
            ->select('g')
            ->from(Guest::class, 'g')
            ->where('g.bookingProduct = :bookingProduct')
            ->setParameter('bookingProduct', $this->getRequest()->get('id'));

        $formMapper
            ->add('totalAmount')
            ->add('currency')
            ->add('product')
            ->add('guests', ModelType::class, array(
                'query'     => $query,
                'multiple'  => true,
            ))
            ->add('rates', ModelType::class, array(
                'multiple' => true,
            ))
        ;
    }

    /**
     *
     * @param ShowMapper $showMapper
     *
     * @return void
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('product')
            ->add('totalAmount')
            ->add('currency')
            ->add('guests')
            ->add('rates')
            ->add('createdAt');
    }
}
