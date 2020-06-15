<?php

namespace spec\App\Form;

use App\Entity\Product;
use App\Form\Bb8GetRoomsType;
use App\Repository\ChannelManagerRepository;
use App\Service\ChannelManager\BB8\Operation\Model\GetRooms;
use App\Service\Loader\PartnerLoader;
use FSevestre\BooleanFormType\Form\Type\BooleanType;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Bb8GetRoomsTypeSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Bb8GetRoomsType::class);
    }

    function let(PartnerLoader $partnerLoader, ChannelManagerRepository $channelManagerRepository)
    {
        $this->beConstructedWith($partnerLoader, $channelManagerRepository);
    }

    function it_builds_form(
        FormBuilderInterface $builder,
        FormFactoryInterface $formFactory
    )
    {
        $builder->add('externalPartnerIds', null, ['property_path' => 'partners'])
            ->shouldBeCalled()
            ->willReturn($builder);
        $builder->add('externalUpdatedFrom', DateTimeType::class,
            [
                'widget'        => 'single_text',
            ])
            ->shouldBeCalled()
            ->willReturn($builder);

        $builder->get('externalPartnerIds')->willReturn($builder);
        $builder->addModelTransformer(Argument::any())->willReturn($builder);
        $builder->get('externalUpdatedFrom')->willReturn($builder);
        $builder->addModelTransformer(Argument::any())->willReturn($builder);
        $builder->getFormFactory()->willReturn($formFactory);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, Argument::any())->shouldBeCalled();

        $this->buildForm($builder, []);
    }

    function it_sets_mapped_class(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => GetRooms::class,
                'allow_extra_fields' => true,
            )
        )
            ->shouldBeCalled();
        $this->configureOptions($resolver);
    }

    function it_gets_block_prefix()
    {
        $this->getBlockPrefix()->shouldBe('');
    }
}
