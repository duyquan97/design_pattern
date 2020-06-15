<?php

namespace App\Admin;

use App\Entity\TransactionStatus;
use App\Entity\Availability;
use App\Model\AvailabilitySource;
use App\Model\Factory\ProductAvailabilityCollectionFactory;
use App\Model\ProductAvailabilityCollection;
use App\Service\BookingEngineInterface;
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
 * Class AvailabilityAdmin
 *
 * @author Smartbox Web Team <si-web@smartbox.com>+
 */
class AvailabilityAdmin extends AbstractAdmin
{
    /**
     * @var array
     */
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'updatedAt',
    );

    /**
     *
     * @var BookingEngineInterface
     */
    private $bookingEngine;

    /**
     * @var ProductAvailabilityCollectionFactory
     */
    private $availabilityCollectionFactory;

    /**
     * PartnerAdmin constructor.
     *
     * @param string                                $code
     * @param string                                $class
     * @param string                                $baseControllerName
     * @param BookingEngineInterface                $bookingEngine
     * @param ProductAvailabilityCollectionFactory  $availabilityCollectionFactory
     */
    public function __construct($code, $class, $baseControllerName, BookingEngineInterface $bookingEngine, ProductAvailabilityCollectionFactory $availabilityCollectionFactory)
    {
        $this->bookingEngine = $bookingEngine;
        $this->availabilityCollectionFactory = $availabilityCollectionFactory;

        parent::__construct($code, $class, $baseControllerName);
    }

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

        $list['import']['template'] = 'ProductAdmin/import_button.html.twig';

        return $list;
    }

    /**
     *
     * @return array
     */
    public function getDashboardActions()
    {
        $actions = parent::getDashboardActions();

        $actions['import']['template'] = 'ProductAdmin/import_dashboard_button.html.twig';

        return $actions;
    }

    /**
     * @return array
     */
    public function getExportFields()
    {
        return [
            'ID' => 'id',
            'Room' => 'product.name',
            'Partner URN' => 'partner.identifier',
            'Date' => 'dateFormatted',
            'Availability' => 'stock',
            'Stop Sale' => 'stopSaleString',
            'Channel Manager' => 'partner.channelManager.name',
            'Created At' => 'createdAtFormatted',
            'Updated At' => 'updatedAtFormatted',
            'Transaction' => 'transaction.transactionId',
        ];
    }

    /**
     * @param Availability $availability
     *
     * @return void
     */
    public function postUpdate($availability) : void
    {
        /** @var Availability $availability */
        $partner = $availability->getPartner();
        $productAvailabilityCollection = $this->availabilityCollectionFactory
            ->create($partner)
            ->setSource(AvailabilitySource::ALIGNMENT)
            ->addAvailability($availability);

        $this->bookingEngine->updateAvailability($productAvailabilityCollection);
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
            ->add('stock', null, ['label' => 'Availability'])
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
            ->add('stopSale')
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
            ->add('partner.channelManager.identifier', null, ['label' => 'Channel Manager']);
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
            ->add('product', null, ['label' => 'Room'])
            ->add('partner.identifier', null, ['label' => 'Partner URN'])
            ->add('date', null, ['format' => 'Y-m-d'])
            ->add('stock', null, ['label' => 'Availability'])
            ->add('stopSale')
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
            ->add(
                'partner',
                ModelAutocompleteType::class,
                [
                    'property' => 'identifier',
                    'label'    => 'Partner',
                ]
            )
            ->add('stock', null, ['label' => 'Availability'])
            ->add(
                'date',
                DatePickerType::class,
                [
                    'format'         => 'yyyy-MM-dd',
                    'dp_use_current' => true,
                    'disabled'        => $this->getSubject()->getId() ? true : false,
                ]
            )
            ->add('stopSale')
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
            ->add('date')
            ->add('stopSale')
            ->add('stock', null, ['label' => 'Availability'])
            ->add('product', null, ['label' => 'Room'])
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
