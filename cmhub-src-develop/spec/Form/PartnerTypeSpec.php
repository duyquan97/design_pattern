<?php

namespace spec\App\Form;

use App\Entity\ChannelManager;
use App\Entity\Partner;
use App\Form\PartnerType;
use Doctrine\ORM\EntityManagerInterface;
use FSevestre\BooleanFormType\Form\Type\BooleanType;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PartnerTypeSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PartnerType::class);
    }

    function let(EntityManagerInterface $entityManager)
    {
        $this->beConstructedWith($entityManager);
    }

    function it_builds_form(FormBuilderInterface $builder, Partner $partner, ChannelManager $channel)
    {
        $builder->getData()->willReturn($partner);
        $partner->getIdentifier()->willReturn('quetepires');
        $partner->getName()->willReturn('jijaju');
        $partner->getCurrency()->willReturn('EUR');
        $partner->getDescription()->willReturn('desc');
        $partner->getStatus()->willReturn('statuspatatus');
        $partner->getChannelManagerHubApiKey()->willReturn('gaytepires');
        $partner->getChannelManager()->willReturn($channel);
        $channel->__toString()->willReturn('wubook');
        $partner->getId()->willReturn('1');

        $builder->add('id', TextType::class, ['property_path' => 'identifier', 'empty_data' => 'quetepires'])
            ->shouldBeCalled()
            ->willReturn($builder);
        $builder->add('displayName', null, ['property_path' => 'name', 'empty_data' => 'jijaju'])
            ->shouldBeCalled()
            ->willReturn($builder);
        $builder->add('currencyCode', null, ['property_path' => 'currency', 'empty_data' => 'EUR'])
            ->shouldBeCalled()
            ->willReturn($builder);
        $builder->add('description', null, ['empty_data' => 'desc'])
            ->shouldBeCalled()
            ->willReturn($builder);
        $builder->add('status', null, ['empty_data' => 'statuspatatus'])
            ->shouldBeCalled()
            ->willReturn($builder);

        $builder->add('channelManagerHubApiKey', null, ['empty_data' => 'gaytepires', 'required' => false])
            ->shouldBeCalled()
            ->willReturn($builder);

        $builder->add('isChannelManagerEnabled', BooleanType::class, ['property_path' => 'enabled'])
            ->shouldBeCalled()
            ->willReturn($builder);

        $builder->add('channelManagerCode', null, ['property_path' => 'channelManager', 'empty_data' => null])
            ->shouldBeCalled()
            ->willReturn($builder);

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            Argument::any()
        )
            ->shouldBeCalled();

        $this->buildForm($builder, []);
    }

    function it_sets_mapped_class(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => Partner::class,
                'allow_extra_fields' => true,
            )
        )->shouldBeCalled();

        $this->configureOptions($resolver);
    }

    function it_gets_block_prefix()
    {
        $this->getBlockPrefix()->shouldBe('');
    }
}
