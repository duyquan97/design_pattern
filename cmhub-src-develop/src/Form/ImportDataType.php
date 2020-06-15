<?php

namespace App\Form;

use App\Entity\ImportData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ImportDataType
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ImportDataType extends AbstractType
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
            ->add('file', FileType::class);
    }

    /**
     *
     * @param OptionsResolver $resolver
     *
     * @return  void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => ImportData::class,
            ]
        );
    }

    /**
     *
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'App_importdata';
    }
}
