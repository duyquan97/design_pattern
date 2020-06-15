<?php

namespace spec\App\Form;

use App\Entity\Product;
use App\Form\ProductType;
use FSevestre\BooleanFormType\Form\Type\BooleanType;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductTypeSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductType::class);
    }

    function let(EntityManagerInterface $entityManager)
    {
        $this->beConstructedWith($entityManager);
    }

    function it_builds_form(FormBuilderInterface $builder, Product $product)
    {
        $builder->getData()->shouldBeCalled()->willReturn($product);
        $product->getName()->shouldBeCalled()->willReturn('PepitodelosPalotes');
        $product->getDescription()->shouldBeCalled()->willReturn('To loco estas jave');
        $builder->add('productCode', null, ['property_path' => 'identifier'])
            ->shouldBeCalled()
            ->willReturn($builder);
        $builder->add('productName', null, ['property_path' => 'name', 'empty_data' => 'PepitodelosPalotes'])
            ->shouldBeCalled()
            ->willReturn($builder);
        $builder->add('productBrief', null, ['property_path' => 'description', 'empty_data' => 'To loco estas jave'])
            ->shouldBeCalled()
            ->willReturn($builder);
        $builder->add('isSellable', BooleanType::class, ['property_path' => 'sellable'])
            ->shouldBeCalled()
            ->willReturn($builder);
        $builder->add('isReservable', BooleanType::class, ['property_path' => 'reservable'])
            ->shouldBeCalled()
            ->willReturn($builder);
        $builder->add('partnerCode', null, ['property_path' => 'partner'])
            ->shouldBeCalled()
            ->willReturn($builder);
        $builder->addEventListener(FormEvents::PRE_SUBMIT,
            Argument::any()
        )
            ->shouldBeCalled();

        $this->buildForm($builder, []);
    }

    function it_sets_mapped_class(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => Product::class,
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
