<?php

namespace App\Admin;

use App\Model\CommissionType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Form\Type\DatePickerType;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Class ExperienceAdmin
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ExperienceAdmin extends AbstractAdmin
{
    /**
     *
     * @param string $action
     * @param null   $object
     *
     * @return array
     */
    public function configureActionButtons($action, $object = null)
    {
        $list = parent::configureActionButtons($action, $object);

        $list['import']['template'] = 'ExperienceAdmin/import_button.html.twig';

        return $list;
    }

    /**
     *
     * @return array
     */
    public function getDashboardActions()
    {
        $actions = parent::getDashboardActions();

        $actions['import']['template'] = 'ExperienceAdmin/import_dashboard_button.html.twig';

        return $actions;
    }

    /**
     * @return array
     */
    public function getExportFields()
    {
        return [
            'ID' => 'id',
            'Identifier' => 'identifier',
            'Name' => 'name',
            'Price' => 'price',
            'Commission' => 'commission',
            'Commission Type' => 'commission_type',
            'Partner' => 'partner.name',
            'Created At' => 'createdAtFormatted',
            'Updated At' => 'updatedAtFormatted',
            'Description' => 'description',
        ];
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
            ->add('import');
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
            ->add('name')
            ->add('identifier')
            ->add(
                'commissionType',
                'doctrine_orm_string',
                [],
                ChoiceType::class,
                [
                    'choices' => CommissionType::CHOICES,
                ]
            )
            ->add('partner.identifier')
            ->add(
                'createdAt',
                'doctrine_orm_date_range',
                [],
                DateRangePickerType::class,
                [
                    'field_options' =>
                        [
                            'format' => 'MMM d, yyyy',
                        ],
                ]
            )
            ->add(
                'updatedAt',
                'doctrine_orm_date_range',
                [],
                DateRangePickerType::class,
                [
                    'field_options' =>
                        [
                            'format' => 'MMM d, yyyy',
                        ],
                ]
            );
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
            ->add('identifier')
            ->add('name')
            ->add('price')
            ->add('commission')
            ->add('commissionType')
            ->add('partner')
            ->add('createdAt')
            ->add('updatedAt')
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
            ->add('identifier')
            ->add('name')
            ->add('price')
            ->add('commission')
            ->add('commissionType', ChoiceType::class, [
                'choices' => [
                    'Percentage' => CommissionType::PERCENTAGE,
                    'Amount' => CommissionType::AMOUNT,
                ],
            ])
            ->add(
                'partner',
                ModelAutocompleteType::class,
                [
                    'property' => 'identifier',
                    'label'    => 'Partner',
                ]
            );
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
            ->add('price')
            ->add('commission')
            ->add('commissionType')
            ->add('partner')
            ->add('createdAt');
    }
}
