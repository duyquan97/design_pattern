<?php

namespace App\Booking\Contract\R2D2;

use App\Booking\Model\Experience;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ExperienceType
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ExperienceType extends AbstractType
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
            ->add('id')
            ->add('components', CollectionType::class, [
                'entry_type' => ExperienceComponentType::class,
                'allow_add'  => true,
            ])
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
            [
                'data_class'         => Experience::class,
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
