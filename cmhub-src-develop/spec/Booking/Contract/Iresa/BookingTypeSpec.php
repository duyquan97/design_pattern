<?php

namespace spec\App\Booking\Contract\Iresa;

use App\Booking\Contract\Iresa\BookingProductType;
use App\Booking\Contract\Iresa\BookingType;
use App\Booking\Model\Booking;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookingTypeSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(BookingType::class);
    }

    function it_builds_form(FormBuilderInterface $builder)
    {
        $builder->add('reservationId', null, ['property_path' => 'identifier'])->shouldBeCalled()->willReturn($builder);
        $builder->add(
            'dateStart',
            DateTimeType::class,
            ['property_path' => 'startDate', 'widget' => 'single_text']
        )
            ->shouldBeCalled()
            ->willReturn($builder);
        $builder->add(
            'dateEnd',
            DateTimeType::class,
            ['property_path' => 'endDate', 'widget' => 'single_text']
        )
            ->shouldBeCalled()
            ->willReturn($builder);
        $builder->add(
            'createDate',
            DateTimeType::class,
            [
                'widget' => 'single_text',
                'property_path' => 'createdAt',
            ]
        )
            ->shouldBeCalled()
            ->willReturn($builder);
        $builder->add('voucherNumber')->shouldBeCalled()->willReturn($builder);
        $builder->add('status')->shouldBeCalled()->willReturn($builder);
        $builder->add('totalAmount',null, ['property_path' => 'price'])->shouldBeCalled()->willReturn($builder);
        $builder->add('currency', CurrencyType::class)->shouldBeCalled()->willReturn($builder);
        $builder->add('requests')->shouldBeCalled()->willReturn($builder);
        $builder->add('comments')->shouldBeCalled()->willReturn($builder);
        $builder->add('experienceId', null, ['property_path' => 'experience.id'])->shouldBeCalled()->willReturn($builder);

        $builder->add('partnerCode', null, ['property_path' => 'partner'])->shouldBeCalled()->willReturn($builder);
        $builder->add(
            'roomTypes',
            CollectionType::class,
            [
                'entry_type' => BookingProductType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ]
        )
            ->shouldBeCalled()
            ->willReturn($builder);
        $builder->get('status')->willReturn($builder);
        $builder->addModelTransformer(Argument::any())->willReturn($builder);

        $this->buildForm($builder, []);
    }

    function it_sets_mapped_class(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Booking::class,
                'allow_extra_fields' => true,
            ]
        )
            ->shouldBeCalled()
            ->willReturn($resolver);
        $this->configureOptions($resolver);
    }

    function it_gets_block_prefix()
    {
        $this->getBlockPrefix()->shouldBe('booking');
    }
}
