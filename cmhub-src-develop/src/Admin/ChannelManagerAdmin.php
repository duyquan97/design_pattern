<?php

namespace App\Admin;

use App\Entity\CmUser;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * Class ChannelManagerAdmin
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ChannelManagerAdmin extends AbstractAdmin
{
    /**
     * @return array
     */
    public function getExportFields()
    {
        return [
            'ID' => 'id',
            'Channel Manager' => 'name',
            'Identifier' => 'identifier',
            'User' => 'user.username',
            'Push Bookings' => 'pushBookingsString',
            'Created At' => 'createdAtFormatted',
            'Updated At' => 'updatedAtFormatted',
        ];
    }

    /**
     *
     * @param DatagridMapper $datagridMapper
     *
     * @return void
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('identifier')
            ->add('name', null, ['label' => 'Channel Manager'])
            ->add('user')
            ->add('pushBookings')
            ->add('createdAt')
            ->add('updatedAt');
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
            ->add('name', null, ['label' => 'Channel Manager'])
            ->add('identifier')
            ->add('user')
            ->add('pushBookings')
            ->add('createdAt')
            ->add('updatedAt')
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
            ->add('identifier')
            ->add('name')
            ->add(
                'user',
                EntityType::class,
                [
                    'required'    => false,
                    'class'       => CmUser::class,
                    'placeholder' => 'Partner level authentication',
                ]
            )
            ->add('pushBookings');
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
            ->add('identifier')
            ->add('name')
            ->add('user')
            ->add('pushBookings')
            ->add('createdAt')
            ->add('updatedAt');
    }
}
