<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

/**
 * Class GuestAdmin
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class GuestAdmin extends AbstractAdmin
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
            ->add('name')
            ->add('surname')
            ->add('email')
            ->add('phone')
            ->add('address')
            ->add('city')
            ->add('zipCode')
            ->add('state')
            ->add('country')
            ->add('countryCode')
            ->add('age')
            ->add('isMain')
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
            ->add('name')
            ->add('surname')
            ->add('email')
            ->add('phone')
            ->add('address')
            ->add('city')
            ->add('zipCode')
            ->add('state')
            ->add('country')
            ->add('countryCode')
            ->add('age')
            ->add('isMain')
            ->add('createdAt')
            ->add(
                '_action',
                null,
                [
                    'actions' => [
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
        $formMapper
            ->add('name')
            ->add('surname')
            ->add('email')
            ->add('phone')
            ->add('address')
            ->add('city')
            ->add('zipCode')
            ->add('state')
            ->add('country')
            ->add('countryCode')
            ->add('age')
            ->add('isMain');
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
            ->add('name')
            ->add('surname')
            ->add('email')
            ->add('phone')
            ->add('address')
            ->add('city')
            ->add('zipCode')
            ->add('state')
            ->add('country')
            ->add('countryCode')
            ->add('age')
            ->add('isMain')
            ->add('createdAt');
    }
}
