<?php

namespace spec\App\Booking\Contract\Iresa;

use App\Booking\Contract\Iresa\GuestType;
use App\Booking\Model\Guest;
use FSevestre\BooleanFormType\Form\Type\BooleanType;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GuestTypeSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(GuestType::class);
    }

    function it_builds_form(FormBuilderInterface $builder)
    {
        $builder->add('name')->shouldBeCalled()->willReturn($builder);
        $builder->add('surname')->shouldBeCalled()->willReturn($builder);
        $builder->add('email')->shouldBeCalled()->willReturn($builder);
        $builder->add('phone')->shouldBeCalled()->willReturn($builder);
        $builder->add('address')->shouldBeCalled()->willReturn($builder);
        $builder->add('city')->shouldBeCalled()->willReturn($builder);
        $builder->add('zip', null, ['property_path' => 'zipCode'])->shouldBeCalled()->willReturn($builder);
        $builder->add('state')->shouldBeCalled()->willReturn($builder);
        $builder->add('country')->shouldBeCalled()->willReturn($builder);
        $builder->add('countryCode')->shouldBeCalled()->willReturn($builder);
        $builder->add('age')->shouldBeCalled()->willReturn($builder);
        $builder->add('isMain',
                BooleanType::class,
                [
                    'property_path' => 'main',
                    'empty_data' => false,
                ]
        )->shouldBeCalled()->willReturn($builder);

        $this->buildForm($builder, []);
    }

    function it_sets_mapped_class(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => Guest::class,
            )
        )->shouldBeCalled();

        $this->configureOptions($resolver);
    }

    function it_gets_block_prefix()
    {
        $this->getBlockPrefix()->shouldBe('');
    }
}
