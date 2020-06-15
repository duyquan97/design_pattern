<?php

namespace App\Form;

use App\Entity\ChannelManager;
use App\Entity\Partner;
use App\Service\ChannelManager\ChannelManagerList;
use Doctrine\ORM\EntityManagerInterface;
use FSevestre\BooleanFormType\Form\Type\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PartnerType
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class PartnerType extends AbstractType
{

    /**
     *
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * PartnerType constructor.
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
        /* @var Partner $partner */
        $partner = $builder->getData();
        $builder
            ->add(
                'id',
                TextType::class,
                [
                    'property_path' => 'identifier',
                    'empty_data'    => $partner->getIdentifier(),
                ]
            )
            ->add(
                'displayName',
                null,
                [
                    'property_path' => 'name',
                    'empty_data'    => $partner->getName(),
                ]
            )
            ->add(
                'currencyCode',
                null,
                [
                    'property_path' => 'currency',
                    'empty_data'    => $partner->getCurrency(),
                ]
            )
            ->add('description', null, ['empty_data' => $partner->getDescription()])
            ->add('status', null, ['empty_data' => $partner->getStatus()])
            ->add(
                'channelManagerHubApiKey',
                null,
                [
                    'empty_data' => $partner->getChannelManagerHubApiKey(),
                    'required'   => false,
                ]
            )
            ->add('isChannelManagerEnabled', BooleanType::class, ['property_path' => 'enabled'])
            ->add(
                'channelManagerCode',
                null,
                [
                    'property_path' => 'channelManager',
                    'empty_data'    => null,
                ]
            )->addEventListener(
                FormEvents::PRE_SUBMIT,
                function (FormEvent $event) {
                    $data = $event->getData();

                    if (array_key_exists('channelManagerCode', $data)) {
                        $channelManager = $this->entityManager->getRepository(ChannelManager::class)->findOneBy(['identifier' => $data['channelManagerCode']]);
                        $data['channelManagerCode'] = $channelManager;
                    }

                    $event->setData($data);
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
                'data_class'         => Partner::class,
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
