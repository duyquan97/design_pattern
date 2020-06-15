<?php

namespace spec\App\Controller;

use App\Controller\ProductController;
use App\Entity\CmUser;
use App\Entity\Experience;
use App\Entity\Factory\ExperienceFactory;
use App\Entity\ChannelManager;
use App\Entity\Partner;
use App\Form\ExperienceType;
use App\Form\PartnerType;
use App\Repository\ExperienceRepository;
use App\Repository\PartnerRepository;
use App\Service\Iresa\IresaBookingEngine;
use App\Utils\FormHelper;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogKey;
use App\Utils\Monolog\LogStatus;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class ProductControllerSpec
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductControllerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductController::class);
    }

    function let(
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        ExperienceFactory $experienceFactory,
        CmhubLogger $cmhubLogger,
        FormHelper $formHelper
    ) {
        $this->beConstructedWith($formFactory, $entityManager, $experienceFactory, $cmhubLogger, $formHelper);
    }

    function it_product_experience(Request $request, EntityManagerInterface $entityManager, ExperienceRepository $experienceRepository,
        FormFactoryInterface $formFactory, FormInterface $form, Experience $experience)
    {
        $experienceArray = [
            'id'                   => '9',
            'identifier'           => '123456789',
            'name'                 => 'My Experience',
            'price'                => 100,
            'commission_type'      => 'percentage',
            'commission'           => 5,
            LogKey::PARTNER_ID_KEY => '00019091',
        ];
        $request->getContent()->willReturn(json_encode($this->request));
        $entityManager->getRepository(Experience::class)->willReturn($experienceRepository);
        $experienceRepository->findOneBy(['identifier' => '123456789'])->willReturn($experience);
        $formFactory->create(ExperienceType::class, $experience)->willReturn($form);
        $form->submit($this->request, false)->shouldBeCalled();
        $form->isValid()->willReturn(true);
        $entityManager->persist($experience)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();
        $experience->toArray()->willReturn($experienceArray);
        $this->productAction($request)->shouldBe([LogKey::STATUS_KEY => LogStatus::SUCCESS]);
    }

    function it_product_experience_form_invalid(Request $request, EntityManagerInterface $entityManager, ExperienceRepository $experienceRepository,
        FormFactoryInterface $formFactory, FormInterface $form, Experience $experience, FormHelper $formHelper)
    {
        $request->getContent()->willReturn(json_encode($this->request));
        $entityManager->getRepository(Experience::class)->willReturn($experienceRepository);
        $experienceRepository->findOneBy(['identifier' => '123456789'])->willReturn($experience);
        $formFactory->create(ExperienceType::class, $experience)->willReturn($form);
        $form->submit($this->request, false)->shouldBeCalled();
        $form->isValid()->willReturn(false);
        $formHelper->getErrorsFromForm($form)->willReturn(['form1' => ['key' =>'value']]);
        $this->productAction($request)->shouldBe([LogKey::STATUS_KEY => LogStatus::SKIPPED, 'errors' => '{"form1":{"key":"value"}}']);
    }

    protected $request = [
        'type'           => 'experience',
        'universe_id'    => 'STA',
        'identifier'    => '123456789',
        'name'           => 'My Experience',
        'price'          => 100,
        'commission'     => 5,
        'commission_type' => 'percentage',
        'partner_code'    => '00019091',
    ];
}