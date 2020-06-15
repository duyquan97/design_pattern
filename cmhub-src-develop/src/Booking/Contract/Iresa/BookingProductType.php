<?php

namespace App\Booking\Contract\Iresa;

use App\Booking\Model\Room;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BookingProductType
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingProductType extends AbstractType
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
            ->add('roomTypeCode', TextType::class, ['property_path' => 'id'])
            ->add('totalAmount')
            ->add('currency', CurrencyType::class)
            ->add(
                'rates',
                CollectionType::class,
                [
                    'entry_type' => BookingProductRateType::class,
                    'allow_add'  => true,
                    'property_path' => 'dailyRates',
                ]
            )
            ->add(
                'guests',
                CollectionType::class,
                [
                    'entry_type' => GuestType::class,
                    'allow_add'  => true,
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
                'data_class' => Room::class,
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
        return '';
    }
}
