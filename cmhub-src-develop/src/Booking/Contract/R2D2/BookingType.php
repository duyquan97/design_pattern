<?php

namespace App\Booking\Contract\R2D2;

use App\Booking\Model\Booking;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
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
     * @param array $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', null, ['property_path' => 'identifier'])
            ->add(
                'created_at',
                DateTimeType::class,
                [
                    'widget' => 'single_text',
                    'property_path' => 'createdAt',
                ]
            )
            ->add(
                'start_date',
                DateType::class,
                [
                    'property_path' => 'startDate',
                    'widget' => 'single_text',
                ]
            )
            ->add(
                'end_date',
                DateType::class,
                [
                    'property_path' => 'endDate',
                    'widget' => 'single_text',
                ]
            )
            ->add('voucher_number', null, ['property_path' => 'voucherNumber'])
            ->add('status')
            ->add('price')
            ->add('currency', CurrencyType::class)
            ->add('experience', ExperienceType::class)
            ->add('partner_id', null, ['property_path' => 'partner'])
            ->add(
                'room_types',
                CollectionType::class,
                [
                    'entry_type' => RoomType::class,
                    'allow_add' => true,
                    'property_path' => 'roomTypes',
                ]
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
                'data_class' => Booking::class,
                'allow_extra_fields' => true,
            ]
        );
    }

    /**
     * @return null|string
     */
    public function getBlockPrefix()
    {
        return '';
    }
}
