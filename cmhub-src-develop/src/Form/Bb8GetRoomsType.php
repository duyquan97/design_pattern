<?php

namespace App\Form;

use App\Exception\PartnerNotFoundException;
use App\Repository\ChannelManagerRepository;
use App\Service\ChannelManager\BB8\BB8ChannelManager;
use App\Service\ChannelManager\BB8\Operation\Model\GetRooms;
use App\Service\Loader\PartnerLoader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class Bb8GetRoomsType
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class Bb8GetRoomsType extends AbstractType
{
    /** @var PartnerLoader $partnerLoader */
    protected $partnerLoader;

    /** @var ChannelManagerRepository $channelManagerRepository */
    protected $channelManagerRepository;

    /**
     * BookingProductType constructor.
     *
     * @param PartnerLoader             $partnerLoader
     * @param ChannelManagerRepository  $channelManagerRepository
     */
    public function __construct(PartnerLoader $partnerLoader, ChannelManagerRepository $channelManagerRepository)
    {
        $this->partnerLoader = $partnerLoader;
        $this->channelManagerRepository = $channelManagerRepository;
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
                'externalPartnerIds',
                null,
                [
                    'property_path' => 'partners',
                ]
            )
            ->add(
                'externalUpdatedFrom',
                DateTimeType::class,
                [
                    'widget'        => 'single_text',
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
                        if (!$ids) {
                            return $this->partnerLoader->findByChannelManager($this->channelManagerRepository->findOneBy(['identifier' => BB8ChannelManager::NAME]));
                        }

                        $partners = $this->partnerLoader->findByIds(explode(',', $ids));
                        if (empty($partners)) {
                            throw new PartnerNotFoundException($ids);
                        }

                        return $partners;
                    }
                )
            );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
                if (!isset($data['externalUpdatedFrom']) && !isset($data['externalPartnerIds'])) {
                    $form->addError(new FormError('Missing parameters'));
                }
                if (isset($data['externalPartnerIds'])) {
                    $identifiers = array_filter(array_map('trim', explode(',', $data['externalPartnerIds'])));
                    if (empty($identifiers)) {
                        $form->addError(new FormError('Parameter externalPartnerIds is empty'));
                    }
                }
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
        $resolver
            ->setDefaults(
                [
                    'data_class'         => GetRooms::class,
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
