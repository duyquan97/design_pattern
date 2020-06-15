<?php

namespace spec\App\Booking\Contract\Iresa;

use App\Booking\Contract\Iresa\BookingProductRateType;
use App\Booking\Contract\Iresa\BookingProductType;
use App\Booking\Contract\Iresa\GuestType;
use App\Booking\Model\Room;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookingProductTypeSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BookingProductType::class);
    }

    function it_builds_form(FormBuilderInterface $builder)
    {
        $builder->add('roomTypeCode', TextType::class, ['property_path' => 'id'])
            ->shouldBeCalled()
            ->willReturn($builder);
        $builder->add('totalAmount')->shouldBeCalled()->willReturn($builder);
        $builder->add('currency', CurrencyType::class)->shouldBeCalled()->willReturn($builder);
        $builder->add('rates',
            CollectionType::class,
            [
                'entry_type' => BookingProductRateType::class,
                'allow_add' => true,
                'property_path' => 'dailyRates',
            ]
        )
            ->shouldBeCalled()
            ->willReturn($builder);
        $builder->add('guests',
            CollectionType::class,
            [
                'entry_type' => GuestType::class,
                'allow_add' => true,
            ]
        )
            ->shouldBeCalled()
            ->willReturn($builder);

        $this->buildForm($builder, []);
    }

    function it_sets_mapped_class(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Room::class,
                'allow_extra_fields' => true,
            ]
        )
            ->shouldBeCalled()
            ->willReturn($resolver);
        $this->configureOptions($resolver);
    }

    function it_gets_block_prefix()
    {
        $this->getBlockPrefix()->shouldBe('');
    }
}
