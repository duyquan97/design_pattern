<?php

namespace App\Booking\Contract\R2D2;

use App\Booking\Model\Guest;
use FSevestre\BooleanFormType\Form\Type\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Country;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

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
     * @param array $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                null,
                [
                    'constraints' => [
                        new NotBlank(),
                        new NotNull(),
                    ],
                ]
            )
            ->add(
                'surname',
                null,
                [
                    'constraints' => [
                        new NotBlank(),
                        new NotNull(),
                    ],
                ]
            )
            ->add('email')
            ->add('phone')
            ->add('age')
            ->add(
                'country_code',
                null,
                [
                    'property_path' => 'countryCode',
                    'constraints' => [
                        new Country(),
                    ],
                ]
            )
            ->add(
                'is_main',
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
                'data_class'         => Guest::class,
                'allow_extra_fields' => true,
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
