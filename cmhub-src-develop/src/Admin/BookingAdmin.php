<?php

namespace App\Admin;

use App\Entity\Booking;
use App\Entity\BookingProduct;
use App\Entity\TransactionStatus;
use App\Booking\Model\BookingStatus;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Form\Type\DatePickerType;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Class BookingAdmin
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingAdmin extends AbstractAdmin
{
    /**
     * @var array
     */
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by'    => 'updatedAt',
    );

    /**
     * @param Booking $booking
     *
     * @return void
     */
    public function preUpdate($booking): void
    {
        /** @var BookingProduct $bookingProduct */
        foreach ($booking->getBookingProducts() as $bookingProduct) {
            $bookingProduct->setBooking($booking);
        }
    }

    /**
     * @param Booking $booking
     *
     * @return void
     */
    public function prePersist($booking): void
    {
        /** @var BookingProduct $bookingProduct */
        foreach ($booking->getBookingProducts() as $bookingProduct) {
            $bookingProduct->setBooking($booking);
        }
    }

    /**
     * @return array
     */
    public function getExportFields()
    {
        return [
            'ID'                    => 'id',
            'Booking ID'            => 'identifier',
            'Check In Date'         => 'startDateFormatted',
            'Check Out Date'        => 'endDateFormatted',
            'Total Amount'          => 'totalAmount',
            'Currency'              => 'currency',
            'Status'                => 'status',
            'Partner'               => 'partner.name',
            'Experience'            => 'experience.identifier',
            'Transaction'           => 'transaction.transactionId',
            'Master Room Booked'    => 'bookingProductsName',
            'Booking Creation Date' => 'createdDateFormatted',
            'Created Date'          => 'createdAt',
            'Last Modified Date'    => 'updatedAt',
            'Request'               => 'requests',
            'Comment'               => 'comments',
            'Voucher Number'        => 'voucherNumber',
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
            ->add('identifier', null, ['label' => 'Booking ID'])
            ->add('partner.identifier', null, ['label' => 'Partner URN'])
            ->add('channelManager')
            ->add('experience.identifier', null, ['label' => 'Experience Id'])
            ->add(
                'createdAt',
                'doctrine_orm_date_range',
                [
                    'label' => 'Booking Creation Date',
                ],
                DateRangePickerType::class,
                [
                    'field_options' =>
                        [
                            'format' => 'MMM d, yyyy',
                        ],
                ]
            )
            ->add(
                'startDate',
                'doctrine_orm_date_range',
                [
                    'label' => 'Check In Date',
                ],
                DateRangePickerType::class,
                [
                    'field_options' =>
                        [
                            'format' => 'MMM d, yyyy',
                        ],
                ]
            )
            ->add(
                'endDate',
                'doctrine_orm_date_range',
                [
                    'label' => 'Check Out Date',
                ],
                DateRangePickerType::class,
                [
                    'field_options' =>
                        [
                            'format' => 'MMM d, yyyy',
                        ],
                ]
            )
            ->add('bookingProducts.product.identifier', null, ['label' => 'Master Room Booked'])
            ->add('currency')
            ->add(
                'status',
                'doctrine_orm_string',
                [],
                ChoiceType::class,
                [
                    'choices' => BookingStatus::CHOICES,
                ]
            )
            ->add(
                'transaction.status',
                'doctrine_orm_string',
                [],
                ChoiceType::class,
                [
                    'choices' => TransactionStatus::CHOICES,
                ]
            )
            ->add('transaction.transactionId', null, ['label' => 'Transaction Id']);
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
            ->add('identifier', null, ['label' => 'Booking ID'])
            ->add('startDate', null, ['label' => 'Check In Date'])
            ->add('endDate', null, ['label' => 'Check Out Date'])
            ->add('totalAmount')
            ->add('currency')
            ->add('status', 'string', ['template' => 'BookingAdmin/list__status_field.html.twig'])
            ->add('partner')
            ->add('experience')
            ->add('channelManager')
            ->add('bookingProducts', null, ['label' => 'Master Room Booked'])
            ->add('createdAt', null, ['label' => 'Booking Creation Date'])
            ->add(
                '_action',
                null,
                [
                    'actions' =>
                        [
                            'show'   => [],
                            'delete' => [],
                            'edit'   => [],
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
        $disabled = false;
        if ($this->isCurrentRoute('edit')) {
            $disabled = true;
        }

        $entityManager = $this->getDatagrid()->getQuery()->getEntityManager(BookingProduct::class);
        $query = $entityManager->createQueryBuilder('bp')
                               ->select('bp')
                               ->from(BookingProduct::class, 'bp')
                               ->where('bp.booking = :booking')
                               ->setParameter('booking', $this->getRequest()->get('id'));

        $formMapper
            ->add('identifier')
            ->add('createdAt', DatePickerType::class, [
                'dp_use_current' => false,
                'format'         => 'dd/MM/yyyy H:m:s',
                'disabled'       => $disabled,
            ])
            ->add('updatedAt', DatePickerType::class, [
                'dp_use_current' => false,
                'format'         => 'dd/MM/yyyy H:m:s',
                'disabled'       => $disabled,
            ])
            ->add('startDate', DatePickerType::class, [
                'dp_use_current' => false,
                'format'         => 'dd/MM/yyyy',
                'disabled'       => $disabled,
            ])
            ->add('endDate', DatePickerType::class, [
                'dp_use_current' => false,
                'format'         => 'dd/MM/yyyy',
                'disabled'       => $disabled,
            ])
            ->add('voucherNumber', null, ['disabled' => $disabled])
            ->add('totalAmount', null, ['disabled' => $disabled])
            ->add('currency', null, ['disabled' => $disabled])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Commit' => 'Commit',
                    'Cancel' => 'Cancel',
                ],
            ])
            ->add(
                'partner',
                ModelAutocompleteType::class,
                [
                    'property' => 'identifier',
                    'label'    => 'Partner',
                    'disabled' => $disabled,
                ]
            )
            ->add(
                'experience',
                ModelAutocompleteType::class,
                [
                    'property' => 'identifier',
                    'label'    => 'Experience',
                    'disabled' => $disabled,
                ]
            )
            ->add(
                'bookingProducts',
                ModelType::class,
                [
                    'query'    => $query,
                    'multiple' => true,
                    'disabled' => $disabled,
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
            ->add('identifier', null, ['label' => 'Booking ID'])
            ->add('transaction')
            ->add('createdAt', null, ['label' => 'Booking Creation Date'])
            ->add('updatedAt')
            ->add('startDate', null, ['label' => 'Check In Date'])
            ->add('endDate', null, ['label' => 'Check Out Date'])
            ->add('voucherNumber')
            ->add('totalAmount')
            ->add('currency')
            ->add('status')
            ->add('requests')
            ->add('comments')
            ->add('partner')
            ->add('channelManager')
            ->add('bookingProducts', null, ['label' => 'Master Room Booked'])
            ->add('experience')
            ->add('mainGuest', null, ['template' => 'BookingAdmin/show_guest_link.html.twig']);
    }
}
