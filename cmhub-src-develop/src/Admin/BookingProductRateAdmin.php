<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Form\Type\DatePickerType;

/**
 * Class BookingProductRateAdmin
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingProductRateAdmin extends AbstractAdmin
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
            ->add('amount')
            ->add('currency')
            ->add('date')
            ->add('createdAt');
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
            ->add('amount')
            ->add('currency')
            ->add('date')
            ->add('bookingProduct')
            ->add('createdAt')
            ->add(
                '_action',
                null,
                [
                    'actions' => [
                        'show'   => [],
                        'edit'   => [],
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
        $formMapper
            ->add('amount')
            ->add('currency')
            ->add('date', DatePickerType::class, [
                'dp_use_current' => false,
                'format'         => 'dd/MM/yyyy H:m:s',
            ])
            ->add('bookingProduct');
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
            ->add('amount')
            ->add('currency')
            ->add('bookingProduct')
            ->add('date')
            ->add('createdAt');
    }
}
