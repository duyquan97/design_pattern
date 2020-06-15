<?php

namespace App\Booking\Contract\Iresa;

use App\Booking\Model\Guest;
use FSevestre\BooleanFormType\Form\Type\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class GuestType
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class GuestType extends AbstractType
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
            ->add('name')
            ->add('surname')
            ->add('email')
            ->add('phone')
            ->add('address')
            ->add('city')
            ->add('zip', null, ['property_path' => 'zipCode'])
            ->add('state')
            ->add('country')
            ->add('countryCode')
            ->add('age')
            ->add(
                'isMain',
                BooleanType::class,
                [
                    'property_path' => 'main',
                    'empty_data' => false,
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
            array(
                'data_class' => Guest::class,
            )
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
