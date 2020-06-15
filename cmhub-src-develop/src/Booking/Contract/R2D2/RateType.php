<?php

namespace App\Booking\Contract\R2D2;

use App\Booking\Model\Rate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RateType
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class RateType extends AbstractType
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
            ->add(
                'date',
                DateType::class,
                [
                    'widget' => 'single_text',
                ]
            )
            ->add('price')
            ;
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
                'data_class' => Rate::class,
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
