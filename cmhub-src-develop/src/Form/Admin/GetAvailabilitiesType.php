<?php

namespace App\Form\Admin;

use App\Service\Loader\PartnerLoader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * Class GetAvailabilitiesType
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class GetAvailabilitiesType extends AbstractType
{
    /** @var PartnerLoader $partnerLoader */
    protected $partnerLoader;

    /**
     * BookingProductType constructor.
     *
     * @param PartnerLoader $partnerLoader
     */
    public function __construct(PartnerLoader $partnerLoader)
    {
        $this->partnerLoader = $partnerLoader;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'partner_id',
                null,
                [

                    'constraints' => [
                        new NotNull(),
                    ],
                ]
            )
            ->add('startDate', DateType::class, ['widget' => 'single_text'])
            ->add('endDate', DateType::class, ['widget' => 'single_text']);
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
