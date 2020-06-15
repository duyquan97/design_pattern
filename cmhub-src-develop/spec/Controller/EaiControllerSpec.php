<?php

namespace spec\App\Controller;

use App\Controller\EaiController;
use App\Entity\ChannelManager;
use App\Entity\CmUser;
use App\Entity\Factory\PartnerFactory;
use App\Entity\Partner;
use App\Form\PartnerType;
use App\Repository\CmUserRepository;
use App\Repository\PartnerRepository;
use App\Service\Iresa\IresaBookingEngine;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogKey;
use App\Utils\Monolog\LogStatus;
use App\Utils\FormHelper;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class EaiControllerSpec
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class EaiControllerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EaiController::class);
    }

    function let(
        EntityManagerInterface $entityManager,
        FormFactoryInterface $formFactory,
        PartnerFactory $partnerFactory,
        UserPasswordEncoderInterface $passwordEncoder,
        CmhubLogger $cmhubLogger,
        CmUserRepository $cmUserRepository,
        FormHelper $formHelper
    ) {
        $this->beConstructedWith($entityManager, $formFactory, $partnerFactory, $passwordEncoder, $cmhubLogger, $cmUserRepository, $formHelper);
    }

    function it_partner_process_with_channel_manager_is_null_form_is_valid_partner_is_disable(
        Request $request,
        PartnerFactory $partnerFactory,
        EntityManagerInterface $entityManager,
        EntityManagerInterface $entityManager1,
        PartnerRepository $partnerRepository,
        Partner $partner,
        ChannelManager $channelManager,
        FormFactoryInterface $formFactory,
        FormInterface $form,
        CmUserRepository $cmUserRepository,
        CmUser $cmUser,
        UserPasswordEncoderInterface $passwordEncoder,
        CmhubLogger $logger,
        IresaBookingEngine $iresaBookingEngine
    )
    {
        $request->getContent()->willReturn(json_encode(['id' => '00019091']));
        $entityManager->getRepository(Partner::class)->willReturn($partnerRepository);
        $partnerFactory->create()->shouldNotBeCalled();
        $partnerRepository->findOneBy(['identifier' => '00019091'])->willReturn($partner);
        $partner->getChannelManager()->willReturn(null);
        $formFactory->create(PartnerType::class, $partner)->willReturn($form);
        $form->handleRequest($request)->shouldBeCalled();
        $form->isValid()->willReturn(true);
        $partner->setConnectedAt(null)->shouldNotBeCalled();
        $channelManager->hasPartnerLevelAuth()->shouldNotBeCalled();

        $partner->getUser()->shouldNotBeCalled();
        $partner->getIdentifier()->shouldNotBeCalled();
        $cmUserRepository->findOneBy(['identifier' => '12321321'])->shouldNotBeCalled();
        $cmUser->setUsername('username')->shouldNotBeCalled();
        $cmUser->setChannelManager($channelManager)->shouldNotBeCalled();
        $partner->getChannelManagerHubApiKey()->shouldNotBeCalled();
        $passwordEncoder->encodePassword($cmUser, 'apikey')->shouldNotBeCalled();
        $cmUser->setPassword('password')->shouldNotBeCalled();
        $entityManager1->persist($cmUser)->shouldNotBeCalled();
        $entityManager1->flush()->shouldNotBeCalled();
        $partner->setUser()->shouldNotBeCalled();
        $cmUser->setChannelManager($channelManager)->shouldNotBeCalled();

        $entityManager->persist($partner)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $iresaBookingEngine->pullProducts($partner)->shouldNotBeCalled();

        $partner->toArray()->shouldBeCalled()->willReturn(['data']);

        $this->partnerAction($request)->shouldBe(['status' => 'success']);
    }

    function it_partner_has_partner_level_auth_and_is_enabled_existing_user(Request $request, EntityManagerInterface $entityManager, PartnerRepository $partnerRepository,
        FormFactoryInterface $formFactory, FormInterface $form, CmUser $cmUser, UserPasswordEncoderInterface $passwordEncoder, IresaBookingEngine $iresaBookingEngine,
        Partner $partner, ChannelManager $channelManager)
    {
        $request->getContent()->willReturn(json_encode($this->request));
        $entityManager->getRepository(Partner::class)->willReturn($partnerRepository);
        $partnerRepository->findOneBy(['identifier' => '00145577'])->willReturn($partner);
        $partner->getChannelManager()->willReturn($channelManager);
        $formFactory->create(PartnerType::class, $partner)->willReturn($form);
        $form->handleRequest($request)->shouldBeCalled();
        $form->isValid()->willReturn(true);
        $partner->getChannelManagerHubApiKey()->willReturn('key');
        $passwordEncoder->encodePassword($cmUser, 'key')->shouldBeCalled()->willReturn('password');
        $cmUser->setPassword('password')->shouldBeCalled()->willReturn($cmUser);
        $entityManager->persist($cmUser)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();
        $cmUser->setChannelManager($channelManager)->shouldBeCalled()->willReturn($cmUser);
        $partner->setUser($cmUser)->shouldBeCalled()->willReturn($partner);
        $entityManager->persist($partner)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();
        $channelManager->hasPartnerLevelAuth()->willReturn(true);
        $partner->getUser()->willReturn($cmUser);
        $partner->isEnabled()->willReturn(true);
        $partner->toArray()->willReturn($this->partnerArray);
        $this->partnerAction($request)->shouldBe([LogKey::STATUS_KEY => LogStatus::SUCCESS]);
    }

    function it_partner_process_with_channel_manager_is_not_null_form_is_valid_partner_and_valid_user(
        Request $request,
        PartnerFactory $partnerFactory,
        EntityManagerInterface $entityManager,
        PartnerRepository $partnerRepository,
        Partner $partner,
        ChannelManager $channelManager,
        FormFactoryInterface $formFactory,
        FormInterface $form,
        CmUserRepository $cmUserRepository,
        CmUser $cmUser,
        CmUser $cmUser1,
        UserPasswordEncoderInterface $passwordEncoder,
        CmhubLogger $logger,
        IresaBookingEngine $iresaBookingEngine
    )
    {
        $request->getContent()->willReturn(json_encode(['id' => '00019091']));
        $entityManager->getRepository(Partner::class)->willReturn($partnerRepository);
        $partnerFactory->create()->shouldNotBeCalled();
        $partnerRepository->findOneBy(['identifier' => '00019091'])->willReturn($partner);
        $partner->getChannelManager()->willReturn($channelManager);
        $formFactory->create(PartnerType::class, $partner)->willReturn($form);
        $form->handleRequest($request)->shouldBeCalled();
        $form->isValid()->willReturn(true);
        $channelManager->hasPartnerLevelAuth()->willReturn(true);
        $partner->getUser()->willReturn(null, $cmUser);
        $partner->getIdentifier()->willReturn('00145577');
        $cmUserRepository->findOneBy(['username' => '00145577'])->shouldBeCalled()->willReturn($cmUser);
        $partner->getChannelManagerHubApiKey()->willReturn('key');
        $passwordEncoder->encodePassword($cmUser, 'key')->shouldBeCalled()->willReturn('password');
        $cmUser->setPassword('password')->shouldBeCalled()->willReturn($cmUser);
        $entityManager->persist($cmUser)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();
        $cmUser->setChannelManager($channelManager)->shouldBeCalled()->willReturn($cmUser);
        $partner->setUser($cmUser)->shouldBeCalled()->willReturn($partner);
        $entityManager->persist($partner)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $partner->isEnabled()->willReturn(true);
        $partner->toArray()->willReturn($this->partnerArray);
        $this->partnerAction($request)->shouldBe([LogKey::STATUS_KEY => LogStatus::SUCCESS]);
    }

    function it_partner_has_partner_level_auth_and_is_enabled_no_existing_user(Request $request, EntityManagerInterface $entityManager, PartnerRepository $partnerRepository,
        FormFactoryInterface $formFactory, FormInterface $form, CmUser $cmUser, UserPasswordEncoderInterface $passwordEncoder, IresaBookingEngine $iresaBookingEngine,
        Partner $partner, ChannelManager $channelManager, CmUserRepository $cmUserRepository)
    {
        $request->getContent()->willReturn(json_encode($this->request));
        $entityManager->getRepository(Partner::class)->willReturn($partnerRepository);
        $partnerRepository->findOneBy(['identifier' => '00145577'])->willReturn($partner);
        $partner->getChannelManager()->willReturn($channelManager);
        $formFactory->create(PartnerType::class, $partner)->willReturn($form);
        $form->handleRequest($request)->shouldBeCalled();
        $form->isValid()->willReturn(true);
        $partner->setConnectedAt(null)->shouldNotBeCalled();
        $channelManager->hasPartnerLevelAuth()->shouldBeCalled()->willReturn(true);

        $partner->getUser()->willReturn($cmUser);
        $partner->getIdentifier()->shouldNotBeCalled();
        $cmUserRepository->findOneBy(['identifier' => '12321321'])->shouldNotBeCalled();

        $partner->getChannelManagerHubApiKey()->willReturn('apikey');
        $passwordEncoder->encodePassword($cmUser, 'apikey')->willReturn('password');
        $cmUser->setPassword('password')->shouldBeCalled()->willReturn($cmUser);

        $cmUser->setChannelManager($channelManager)->shouldBeCalled()->willReturn($cmUser);
        $partner->setUser($cmUser)->shouldBeCalled()->willReturn($partner);

        $entityManager->persist($cmUser)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $entityManager->persist($partner)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();
        $partner->toArray()->shouldBeCalled()->willReturn(['data']);

        $this->partnerAction($request)->shouldBe(['status' => 'success']);
    }

    function it_partner_has_partner_level_auth_and_is_disable(Request $request, EntityManagerInterface $entityManager, PartnerRepository $partnerRepository,
        FormFactoryInterface $formFactory, FormInterface $form, CmUser $cmUser, UserPasswordEncoderInterface $passwordEncoder, IresaBookingEngine $iresaBookingEngine,
        Partner $partner, ChannelManager $channelManager)
    {
        $request->getContent()->willReturn(json_encode($this->request));
        $entityManager->getRepository(Partner::class)->willReturn($partnerRepository);
        $partnerRepository->findOneBy(['identifier' => '00145577'])->willReturn($partner);
        $partner->getChannelManager()->willReturn($channelManager);
        $formFactory->create(PartnerType::class, $partner)->willReturn($form);
        $form->handleRequest($request)->shouldBeCalled();
        $form->isValid()->willReturn(true);
        $channelManager->hasPartnerLevelAuth()->willReturn(false);
        $partner->setUser(null)->shouldBeCalled()->willReturn($partner);
        $entityManager->persist($partner)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();
        $partner->isEnabled()->willReturn(true);
        $partner->getUser()->willReturn(null);
        $partner->toArray()->willReturn($this->partnerArray);
        $this->partnerAction($request)->shouldBe([LogKey::STATUS_KEY => LogStatus::SUCCESS]);
    }

    function it_partner_has_form_invalid(Request $request, EntityManagerInterface $entityManager, PartnerRepository $partnerRepository,
        FormFactoryInterface $formFactory, FormInterface $form, FormHelper $formHelper,
        Partner $partner, ChannelManager $channelManager)
    {
        $request->getContent()->willReturn(json_encode($this->request));
        $entityManager->getRepository(Partner::class)->willReturn($partnerRepository);
        $partnerRepository->findOneBy(['identifier' => '00145577'])->willReturn($partner);
        $partner->getChannelManager()->willReturn($channelManager);
        $formFactory->create(PartnerType::class, $partner)->willReturn($form);
        $form->handleRequest($request)->shouldBeCalled();
        $form->isValid()->willReturn(false);
        $formHelper->getErrorsFromForm($form)->willReturn(['form1' => ['key' =>'value']]);
        $this->partnerAction($request)->shouldBe([LogKey::STATUS_KEY => LogStatus::SKIPPED, 'errors' => '{"form1":{"key":"value"}}']);
    }

    function it_partner_no_partner_level_auth_and_is_enabled(Request $request, EntityManagerInterface $entityManager, PartnerRepository $partnerRepository,
        FormFactoryInterface $formFactory, FormInterface $form, CmUser $cmUser, UserPasswordEncoderInterface $passwordEncoder, IresaBookingEngine $iresaBookingEngine,
        Partner $partner, ChannelManager $channelManager)
    {
        $request->getContent()->willReturn(json_encode($this->request));
        $entityManager->getRepository(Partner::class)->willReturn($partnerRepository);
        $partnerRepository->findOneBy(['identifier' => '00145577'])->willReturn($partner);
        $partner->getChannelManager()->willReturn($channelManager);
        $formFactory->create(PartnerType::class, $partner)->willReturn($form);
        $form->handleRequest($request)->shouldBeCalled();
        $form->isValid()->willReturn(true);
        $channelManager->hasPartnerLevelAuth()->willReturn(false);
        $partner->setUser(null)->shouldBeCalled()->willReturn($partner);
        $entityManager->persist($partner)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();
        $partner->isEnabled()->willReturn(true);
        $partner->getUser()->willReturn(null);
        $partner->toArray()->willReturn($this->partnerArray);
        $this->partnerAction($request)->shouldBe([LogKey::STATUS_KEY => LogStatus::SUCCESS]);
    }

    function it_partner_no_partner_level_auth_and_is_disabled(Request $request, EntityManagerInterface $entityManager, PartnerRepository $partnerRepository,
        FormFactoryInterface $formFactory, FormInterface $form, CmUser $cmUser, UserPasswordEncoderInterface $passwordEncoder, IresaBookingEngine $iresaBookingEngine,
        Partner $partner, ChannelManager $channelManager)
    {
        $request->getContent()->willReturn(json_encode($this->request));
        $entityManager->getRepository(Partner::class)->willReturn($partnerRepository);
        $partnerRepository->findOneBy(['identifier' => '00145577'])->willReturn($partner);
        $partner->getChannelManager()->willReturn($channelManager);
        $formFactory->create(PartnerType::class, $partner)->willReturn($form);
        $form->handleRequest($request)->shouldBeCalled();
        $form->isValid()->willReturn(true);
        $channelManager->hasPartnerLevelAuth()->willReturn(false);
        $partner->setUser(null)->shouldBeCalled()->willReturn($partner);
        $entityManager->persist($partner)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();
        $partner->isEnabled()->willReturn(false);
        $partner->getUser()->willReturn(null);
        $partner->toArray()->willReturn($this->partnerArray);
        $this->partnerAction($request)->shouldBe([LogKey::STATUS_KEY => LogStatus::SUCCESS]);
    }

    protected $partnerArray = [
        'id' => '00145577',
        'name' => 'Wubook Partner 1',
        'description' => 'test partner 00145577',
        'status' => 'partner',
        'channel' => [
            'id'            => 'wubook',
            'name'          => 'Test CM Wubook',
            'identifier'    => 'wubook',
            'push_bookings' => false,
        ],
        'user' => [
            'username' => 'wubook',
            'cm'       => 'wubook',
        ]
    ];

    protected $request = [
        'id'                      => '00145577',
        'channelManagerCode'      => 'wubook',
        'displayName'             => 'Name',
        'currencyCode'            => 'SEK',
        'description'             => 'description',
        'channelManagerHubApiKey' => 'whatever',
        'isChannelManagerEnabled' => true,
        'status'                  => 'partner',
    ];
}