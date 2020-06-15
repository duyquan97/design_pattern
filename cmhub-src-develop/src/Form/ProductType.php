<?php

namespace App\Form;

use App\Entity\Partner;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use FSevestre\BooleanFormType\Form\Type\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProductType
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductType extends AbstractType
{

    /**
     *
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * ProductType constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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
        /* @var Product $product */
        $product = $builder->getData();
        $builder
            ->add('productCode', null, ['property_path' => 'identifier'])
            ->add('productName', null, [
                'property_path' => 'name',
                'empty_data'    => $product->getName(),
            ])
            ->add('productBrief', null, [
                'property_path' => 'description',
                'empty_data'    => $product->getDescription(),
            ])
            ->add('isSellable', BooleanType::class, ['property_path' => 'sellable'])
            ->add('isReservable', BooleanType::class, ['property_path' => 'reservable'])
            ->add('partnerCode', null, ['property_path' => 'partner'])
            ->addEventListener(
                FormEvents::PRE_SUBMIT,
                function (FormEvent $event) {
                    $data = $event->getData();
                    $modelData = $event->getForm()->getData();

                    if (!array_key_exists('partnerCode', $data)) {
                        return;
                    }

                    $partner = $this->entityManager->getRepository(Partner::class)->findOneBy(['identifier' => $data['partnerCode']]);

                    $data['partnerCode'] = $partner;
                    $event->setData($data);
                    $modelData->setPartner($partner);
                }
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
                'data_class' => Product::class,
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
