<?php

namespace App\Booking\Contract\Iresa;

use App\Booking\Model\Rate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RateType
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingProductRateType extends AbstractType
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
            ->add('amount', null, ['property_path' => 'price'])
            ->add('currency', CurrencyType::class)
            ->add(
                'date',
                DateTimeType::class,
                ['widget' => 'single_text']
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
                'data_class' => Rate::class,
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
