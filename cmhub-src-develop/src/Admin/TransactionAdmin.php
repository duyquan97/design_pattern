<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\TransactionChannel;
use App\Entity\TransactionStatus;
use App\Entity\TransactionType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Class TransactionAdmin
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
final class TransactionAdmin extends AbstractAdmin
{
    /**
     * @var TransactionChannel
     */
    private $transactionChannel;

    /**
     * @var array
     */
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'updatedAt',
    );

    /**
     * TransactionAdmin constructor.
     *
     * @param string             $code
     * @param string             $class
     * @param string             $baseControllerName
     * @param TransactionChannel $transactionChannel
     */
    public function __construct($code, $class, $baseControllerName, TransactionChannel $transactionChannel)
    {
        $this->transactionChannel = $transactionChannel;
        parent::__construct($code, $class, $baseControllerName);
    }

    /**
     *
     * @param DatagridMapper $datagridMapper
     *
     * @return void
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('transactionId')
            ->add('statusCode')
            ->add(
                'type',
                'doctrine_orm_string',
                [],
                ChoiceType::class,
                [
                    'choices' => TransactionType::CHOICES,
                ]
            )
            ->add(
                'status',
                'doctrine_orm_string',
                [],
                ChoiceType::class,
                [
                    'choices' => TransactionStatus::CHOICES,
                ]
            )
            ->add('partner.identifier', null, ['label' => 'Partner URN'])
            ->add(
                'channel',
                'doctrine_orm_string',
                [],
                ChoiceType::class,
                [
                    'choices' => $this->transactionChannel->getChannels(),
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
            ->add(
                'sentAt',
                'doctrine_orm_date_range',
                [
                    'label' => 'Request Date',
                ],
                DateRangePickerType::class,
                [
                    'field_options' =>
                        [
                            'format' => 'MMM d, yyyy',
                        ],
                ]
            )
        ;
    }

    /**
     *
     * @param ListMapper $listMapper
     *
     * @return void
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('transactionId')
            ->add(
                'transaction_entity',
                null,
                [
                    'label'    => 'Entity',
                    'template' => 'Transaction/list__entity_related.html.twig',
                ]
            )
            ->add('statusCode')
            ->add('status')
            ->add('type')
            ->add('channel')
            ->add('partner')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('sentAt')
            ->add('retries')
            ->add('_action', null, [
                'actions' => [
                    'show'   => [],
                    'edit'   => [],
                    'delete' => [],
                ],
            ]);
    }

    /**
     *
     * @param FormMapper $formMapper
     *
     * @return void
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add(
                'status',
                ChoiceType::class,
                [
                    'choices' => TransactionStatus::CHOICES,
                ]
            )
            ->add(
                'channel',
                ChoiceType::class,
                [
                    'choices' => TransactionChannel::CHOICES,
                ]
            )
            ->add('transactionId', null, ['disabled' => true])
            ->add(
                'request',
                null,
                [
                    'disabled' => true,
                ]
            )
            ->add('response', null, ['disabled' => true])
            ->add('statusCode', null, ['disabled' => true]);
    }

    /**
     *
     * @param ShowMapper $showMapper
     *
     * @return void
     */
    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->add('id')
            ->add('transactionId')
            ->add('response')
            ->add('statusCode')
            ->add('createdAt')
            ->add('sentAt')
            ->add('updatedAt')
            ->add('status')
            ->add('type')
            ->add('partner')
            ->add('channel');
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
            ->remove('create')
            ->remove('edit')
            ->add('entity', $this->getRouterIdParameter().'/entity');
    }
}
