<?php

namespace App\Form;

use App\Exception\PartnerNotFoundException;
use App\Service\ChannelManager\BB8\Operation\Model\GetBookings;
use App\Service\Loader\PartnerLoader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * Class Bb8GetBookingsType
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class Bb8GetBookingsType extends AbstractType
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
                'startDate',
                DateTimeType::class,
                [
                    'widget'      => 'single_text',
                    'constraints' => [
                        new NotBlank(),
                        new NotNull(),
                    ],
                    'empty_data'  => false,
                ]
            )
            ->add(
                'endDate',
                DateTimeType::class,
                [
                    'widget'      => 'single_text',
                    'constraints' => [
                        new NotBlank(),
                        new NotNull(),
                    ],
                    'empty_data'  => false,
                ]
            )
            ->add(
                'externalPartnerIds',
                null,
                [
                    'property_path' => 'partners',
                    'constraints'   => [
                        new NotNull(),
                    ],
                ]
            );

        $builder
            ->get('externalPartnerIds')
            ->addModelTransformer(
                new CallbackTransformer(
                    function ($values) {
                        if (!$values) {
                            return null;
                        }

                        return implode(',', array_map(function ($value) {
                            return $value->getIdentifier();
                        }, $values));
                    },
                    function ($ids) {
                        $partners = $this->partnerLoader->findByIds(explode(',', $ids));
                        if (empty($partners)) {
                            throw new PartnerNotFoundException($ids);
                        }

                        return $partners;
                    }
                )
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
        $resolver
            ->setDefaults(
                [
                    'data_class'         => GetBookings::class,
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
