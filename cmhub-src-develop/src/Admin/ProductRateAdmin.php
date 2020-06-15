<?php

namespace App\Admin;

use App\Entity\TransactionStatus;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Form\Type\DatePickerType;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Class ProductRateAdmin
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductRateAdmin extends AbstractAdmin
{
    /**
     * @var array
     */
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'updatedAt',
    );

    /**
     * @return array
     */
    public function getExportFields()
    {
        return [
            'ID' => 'id',
            'Partner URN' => 'partner.identifier',
            'Room' => 'product.name',
            'Date' => 'dateFormatted',
            'Amount' => 'amount',
            'Channel Manager'  => 'partner.channelManager.name',
            'Created At' => 'createdAtFormatted',
            'Updated At' => 'updatedAtFormatted',
            'Transaction' => 'transaction.transactionId',
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
            ->add('amount')
            ->add(
                'date',
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
            )
            ->add('product.identifier', null, ['label' => 'Room'])
            ->add(
                'transaction.status',
                'doctrine_orm_string',
                [],
                ChoiceType::class,
                [
                    'choices' => TransactionStatus::CHOICES,
                ]
            )
            ->add('transaction.transactionId', null, ['label' => 'Transaction Id'])
            ->add('partner.identifier', null, ['label' => 'Partner URN'])
            ->add('partner.channelManager', null, ['label' => 'Channel Manager']);
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
            ->add('partner.identifier', null, ['label' => 'Partner URN'])
            ->add('product', null, ['label' => 'Room'])
            ->add('date', null, ['format' => 'Y-m-d'])
            ->add('amount')
            ->add('partner.channelManager', null, ['label' => 'Channel Manager'])
            ->add('createdAt', null, ['format' => 'Y-m-d H:i:s'])
            ->add('updatedAt', null, ['format' => 'Y-m-d H:i:s'])
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
        $formMapper
            ->add(
                'partner',
                ModelAutocompleteType::class,
                [
                    'property' => 'identifier',
                    'label'    => 'Partner',
                ]
            )
            ->add('amount')
            ->add(
                'date',
                DatePickerType::class,
                [
                    'format'         => 'Y-mm-dd',
                    'dp_use_current' => true,
                ]
            )
            ->add(
                'date',
                DatePickerType::class,
                [
                    'format'         => 'yyyy-MM-dd',
                    'dp_use_current' => true,
                ]
            )
            ->add(
                'product',
                ModelAutocompleteType::class,
                [
                    'property' => 'identifier',
                    'label'    => 'Room',
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
            ->add('amount')
            ->add('date', null, ['format' => 'Y-m-d'])
            ->add('createdAt')
            ->add('updatedAt')
            ->add('transaction', null, [
                'route' => [
                    'name' => 'show',
                ],
                'label' => 'Transaction ID',
            ]);
    }
}
