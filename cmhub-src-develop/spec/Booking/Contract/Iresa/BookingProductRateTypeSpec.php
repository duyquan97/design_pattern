<?php

namespace spec\App\Booking\Contract\Iresa;

use App\Booking\Contract\Iresa\BookingProductRateType;
use App\Booking\Model\Rate;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookingProductRateTypeSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BookingProductRateType::class);
    }

    function it_builds_form(FormBuilderInterface $builder)
    {
        $builder->add('amount', null, ['property_path' => 'price'])->shouldBeCalled()->willReturn($builder);
        $builder->add('currency', CurrencyType::class)->shouldBeCalled()->willReturn($builder);
        $builder
            ->add(
                'date',
                DateTimeType::class,
                [
                    'widget' => 'single_text',
                ]
            )
            ->shouldBeCalled()
            ->willReturn($builder);

        $this->buildForm($builder, []);
    }

    function it_sets_mapped_class(OptionsResolver $resolver) {
        $resolver->setDefaults(
                [
                    'data_class' => Rate::class,
                    'allow_extra_fields' => true,
                ]
            )
            ->shouldBeCalled()
            ->willReturn($resolver);

        $this->configureOptions($resolver);
    }

    function it_gets_block_prefix() {
        $this->getBlockPrefix()->shouldBe('');
    }
}
