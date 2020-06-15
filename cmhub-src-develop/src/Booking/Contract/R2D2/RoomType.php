<?php

namespace App\Booking\Contract\R2D2;

use App\Booking\Model\Room;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RoomType
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class RoomType extends AbstractType
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
            ->add('id')
            ->add('name')
            ->add(
                'daily_rates',
                CollectionType::class,
                [
                    'entry_type'    => RateType::class,
                    'allow_add'     => true,
                    'property_path' => 'dailyRates',
                ]
            )
            ->add(
                'guests',
                CollectionType::class,
                [
                    'entry_type'    => GuestType::class,
                    'allow_add'     => true,
                    'property_path' => 'guests',
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
                'data_class'         => Room::class,
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
