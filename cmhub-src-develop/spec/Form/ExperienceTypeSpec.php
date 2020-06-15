<?php

namespace spec\App\Form;

use App\Entity\Experience;
use App\Entity\Partner;
use App\Repository\PartnerRepository;
use App\Form\ExperienceType;
use App\Model\UniverseIdType;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExperienceTypeSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ExperienceType::class);
    }

    function let(PartnerRepository $partnerRepository)
    {
        $this->beConstructedWith($partnerRepository);
    }

    function it_builds_form(FormBuilderInterface $builder, Experience $experience, Partner $partner)
    {
        $builder->getData()->shouldBeCalled()->willReturn($experience);
        $experience->getName()->shouldBeCalled()->willReturn('experience_name');
        $experience->getPrice()->shouldBeCalled()->willReturn(5);
        $experience->getPartner()->shouldBeCalled()->willReturn($partner);
        $partner->__toString()->willReturn('partner_id');
        $builder->add('identifier', TextType::class)
            ->shouldBeCalled()
            ->willReturn($builder);
        $builder->add('universe_id', ChoiceType::class,
            [
                'choices' => array_combine(
                    UniverseIdType::EXPERIENCE_TYPES,
                    UniverseIdType::EXPERIENCE_TYPES
                ),
                'mapped'  => false,
            ])
            ->shouldBeCalled()
            ->willReturn($builder);
        $builder->add('type', ChoiceType::class,
            [
                'choices' => array_combine(
                    \App\Model\ProductType::PRODUCT_TYPES,
                    \App\Model\ProductType::PRODUCT_TYPES
                ),
                'mapped'  => false,
            ])
            ->shouldBeCalled()
            ->willReturn($builder);
        $builder->add('name', TextType::class, ['empty_data' => 'experience_name'])
            ->shouldBeCalled()
            ->willReturn($builder);
        $builder->add('description', TextType::class, ['empty_data' => 'experience_name'])
            ->shouldBeCalled()
            ->willReturn($builder);
        $builder->add('price', null, ['empty_data' => 5])
            ->shouldBeCalled()
            ->willReturn($builder);
        $builder->add('commission', null, [
            'required' => false,
        ])->shouldBeCalled()
            ->willReturn($builder);
        $builder->add('commission_type',
            TextType::class,
            [
                'property_path' => 'commissionType',
                'required' => false,
            ])->shouldBeCalled()
            ->willReturn($builder);
        $builder->add('partner_code',
            null,
            [
                'property_path' => 'partner',
                'empty_data'    => $partner,
            ])->shouldBeCalled()
            ->willReturn($builder);

        $builder->get('partner_code')->willReturn($builder);

        $builder->addModelTransformer(
            Argument::any()
        )
            ->shouldBeCalled();

        $this->buildForm($builder, []);
    }

    function it_sets_mapped_class(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => Experience::class,
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
