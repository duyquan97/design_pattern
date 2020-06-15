<?php

namespace App\Form;

use App\Entity\Experience;
use App\Entity\Partner;
use App\Repository\PartnerRepository;
use App\Model\ProductType;
use App\Model\UniverseIdType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\CallbackTransformer;

/**
 * Class ExperienceType
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ExperienceType extends AbstractType
{
    /**
     * @var PartnerRepository
     */
    private $partnerRepository;

    /**
     * BookingType constructor.
     *
     * @param PartnerRepository $partnerRepository
     */
    public function __construct(PartnerRepository $partnerRepository)
    {
        $this->partnerRepository = $partnerRepository;
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
        $entity = $builder->getData();

        $builder
            ->add('identifier', TextType::class)
            ->add(
                'universe_id',
                ChoiceType::class,
                [
                    'choices' => array_combine(
                        UniverseIdType::EXPERIENCE_TYPES,
                        UniverseIdType::EXPERIENCE_TYPES
                    ),
                    'mapped'  => false,
                ]
            )
            ->add(
                'type',
                ChoiceType::class,
                [
                    'choices' => array_combine(
                        ProductType::PRODUCT_TYPES,
                        ProductType::PRODUCT_TYPES
                    ),
                    'mapped'  => false,
                ]
            )
            ->add('name', TextType::class, ['empty_data' => $entity->getName()])
            ->add('description', TextType::class, ['empty_data' => $entity->getName()])
            ->add('price', null, ['empty_data' => $entity->getPrice()])
            ->add('commission', null, [
                'required' => false,
            ])
            ->add(
                'commission_type',
                TextType::class,
                [
                    'property_path' => 'commissionType',
                    'required' => false,
                ]
            )
            ->add(
                'partner_code',
                null,
                [
                    'property_path' => 'partner',
                    'empty_data'    => $entity->getPartner() ? $entity->getPartner() : null,
                ]
            );

        $builder
            ->get('partner_code')
            ->addModelTransformer(
                new CallbackTransformer(
                    function ($id) {
                        if (!$id) {
                            return;
                        }

                        return $id;
                    },
                    function ($partner) {
                        if (!$partner) {
                            return;
                        }

                        $result = $this->partnerRepository->findOneBy(['identifier' => $partner]);
                        if (!$result) {
                            return (new Partner())->setIdentifier($partner);
                        }

                        return $result;
                    }
                )
            );
    }

    /**
     *
     * @param FormEvent $event
     *
     * @return void
     */
    public function listener(FormEvent $event)
    {
        $data = $event->getData();
        if (array_key_exists('partner_code', $data)) {
            $partner = $this->partnerRepository
                ->findOneBy(['identifier' => $data['partner_code']]);
            $data['partner_code'] = $partner;
        }

        $event->setData($data);
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
     *
     * @return null|string
     */
    public function getBlockPrefix()
    {
        return '';
    }
}
