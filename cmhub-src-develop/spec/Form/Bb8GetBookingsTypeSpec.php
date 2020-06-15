<?php

namespace spec\App\Form;

use App\Form\Bb8GetBookingsType;
use App\Service\ChannelManager\BB8\Operation\Model\GetBookings;
use App\Service\Loader\PartnerLoader;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class Bb8GetBookingsTypeSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Bb8GetBookingsType::class);
    }

    function let(PartnerLoader $partnerLoader)
    {
        $this->beConstructedWith($partnerLoader);
    }

    function it_builds_form(
        FormBuilderInterface $builder
    )
    {
        $builder->add(
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
            )->shouldBeCalled()
            ->willReturn($builder);
        $builder->add(
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
            )->shouldBeCalled()
            ->willReturn($builder);
        $builder->add(
                'externalPartnerIds',
                null,
                [
                    'property_path' => 'partners',
                    'constraints'   => [
                        new NotNull(),
                    ],
                ]
            )->shouldBeCalled()
            ->willReturn($builder);


        $builder->get('externalPartnerIds')->willReturn($builder);
        $builder->addModelTransformer(Argument::any())->willReturn($builder);

        $this->buildForm($builder, []);
    }

    function it_sets_mapped_class(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => GetBookings::class,
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
