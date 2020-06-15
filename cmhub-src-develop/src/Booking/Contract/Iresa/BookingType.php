<?php

namespace App\Booking\Contract\Iresa;

use App\Booking\Model\Booking;
use App\Booking\Model\BookingStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BookingType
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingType extends AbstractType
{
    /**
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('reservationId', null, ['property_path' => 'identifier'])
            ->add(
                'dateStart',
                DateTimeType::class,
                [
                    'widget'        => 'single_text',
                    'property_path' => 'startDate',
                ]
            )
            ->add(
                'dateEnd',
                DateTimeType::class,
                [
                    'widget' => 'single_text',
                    'property_path' => 'endDate',
                ]
            )
            ->add(
                'createDate',
                DateTimeType::class,
                [
                    'widget' => 'single_text',
                    'property_path' => 'createdAt',
                ]
            )
            ->add('voucherNumber')
            ->add('status')
            ->add('totalAmount', null, ['property_path' => 'price'])
            ->add('currency', CurrencyType::class)
            ->add('requests')
            ->add('comments')
            ->add('experienceId', null, ['property_path' => 'experience.id'])
            ->add('partnerCode', null, ['property_path' => 'partner'])
            ->add(
                'roomTypes',
                CollectionType::class,
                [
                    'entry_type'    => BookingProductType::class,
                    'allow_add'     => true,
                    'allow_delete'  => true,
                    'by_reference'  => false,
                ]
            );

        $builder->get('status')
            ->addModelTransformer(
                new CallbackTransformer(
                    function ($lowerString) {
                        if (Booking::CONFIRM === $lowerString) {
                            return BookingStatus::CONFIRMED;
                        }

                        return ucfirst($lowerString);
                    },
                    function ($capitalizeString) {
                        if (BookingStatus::CONFIRMED === $capitalizeString) {
                            return Booking::CONFIRM;
                        }

                        return strtolower($capitalizeString);
                    }
                )
            );
    }

    /**
     *
     * @param OptionsResolver $resolver
     *
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => Booking::class,
                'allow_extra_fields' => true,
            ]
        );
    }

    /**
     *
     * @return null|string
     */
    public function getBlockPrefix()
    {
        return 'booking';
    }
}
