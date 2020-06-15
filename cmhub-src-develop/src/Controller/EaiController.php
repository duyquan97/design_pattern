<?php

namespace App\Controller;

use App\Entity\ChannelManager;
use App\Entity\CmUser;
use App\Entity\Factory\ExperienceFactory;
use App\Entity\Factory\PartnerFactory;
use App\Entity\Partner;
use App\Repository\CmUserRepository;
use App\Form\PartnerType;
use App\Service\ChannelManager\ChannelManagerList;
use App\Service\Iresa\IresaBookingEngine;
use App\Utils\FormHelper;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use App\Utils\Monolog\LogKey;
use App\Utils\Monolog\LogStatus;
use App\Utils\Monolog\LogType;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Monolog\Logger;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class EaiController
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class EaiController
{
    /**
     *
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     *
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     *
     * @var PartnerFactory
     */
    private $partnerFactory;

    /**
     *
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     *
     * @var CmhubLogger
     */
    private $logger;

    /**
     * @var CmUserRepository
     */
    private $cmUserRepository;

    /**
     * @var FormHelper
     */
    private $formHelper;

    /**
     * EaiController constructor.
     *
     * @param EntityManagerInterface       $entityManager
     * @param FormFactoryInterface         $formFactory
     * @param PartnerFactory               $partnerFactory
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param CmhubLogger                  $logger
     * @param CmUserRepository             $cmUserRepository
     * @param FormHelper                   $formHelper
     */
    public function __construct(EntityManagerInterface $entityManager, FormFactoryInterface $formFactory, PartnerFactory $partnerFactory, UserPasswordEncoderInterface $passwordEncoder, CmhubLogger $logger, CmUserRepository $cmUserRepository, FormHelper $formHelper)
    {
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        $this->partnerFactory = $partnerFactory;
        $this->passwordEncoder = $passwordEncoder;
        $this->logger = $logger;
        $this->cmUserRepository = $cmUserRepository;
        $this->formHelper = $formHelper;
    }

    /**
     *
     * @param Request $request
     *
     * @return array
     */
    public function partnerAction(Request $request)
    {
        $timeStart = microtime(true);
        $data = json_decode($request->getContent(), true);
        $partner = $this->getPartner($data);

        $form = $this->formFactory->create(PartnerType::class, $partner);

        $form->handleRequest($request);

        if (!$form->isValid()) {
            $this
                ->logger
                ->addRecord(
                    Logger::INFO,
                    'EAI API Request',
                    [
                        LogKey::TYPE_KEY          => LogType::EAI,
                        LogKey::STATUS_KEY        => LogStatus::PARTNER_NOT_VALID,
                        LogKey::ACTION_KEY        => LogAction::PARTNER_FLOW,
                        LogKey::VALIDATION_ERRORS => json_encode($this->formHelper->getErrorsFromForm($form)),
                        LogKey::RESPONSE_TIME_KEY => microtime(true) - $timeStart,
                        LogKey::REQUEST_KEY       => $request->getContent(),
                    ],
                    $this
                );

            return [
                LogKey::STATUS_KEY        => LogStatus::SKIPPED,
                LogKey::VALIDATION_ERRORS => json_encode($this->formHelper->getErrorsFromForm($form)),
            ];
        }

        $channelManager = $partner->getChannelManager();
        if ($channelManager && !$channelManager->hasPartnerLevelAuth()) {
            $partner = $partner->setUser(null);
        }

        if ($channelManager && $channelManager->hasPartnerLevelAuth()) {
            $partner = $this->setUser($partner);
        }

        $this->entityManager->persist($partner);
        $this->entityManager->flush();

        $this
            ->logger
            ->addRecord(
                Logger::INFO,
                'EAI API Request',
                [
                    LogKey::TYPE_KEY          => LogType::EAI,
                    LogKey::STATUS_KEY        => LogStatus::SUCCESS,
                    LogKey::ACTION_KEY        => LogAction::PARTNER_FLOW,
                    LogKey::PARTNER_KEY       => $partner->toArray(),
                    LogKey::REQUEST_KEY       => $request->getContent(),
                    LogKey::RESPONSE_TIME_KEY => microtime(true) - $timeStart,
                ],
                $this
            );

        return [LogKey::STATUS_KEY => LogStatus::SUCCESS];
    }

    /**
     *
     * @Rest\View()
     *
     * @return array
     */
    public function smokeTestAction()
    {
        return [];
    }

    /**
     * TODO: Move this to a service
     *
     * @param array $data
     *
     * @return Partner
     */
    private function getPartner(array $data): Partner
    {
        if (!array_key_exists('id', $data)) {
            return $this->partnerFactory->create();
        }

        /* @var Partner $partner */
        $partner = $this->entityManager->getRepository(Partner::class)->findOneBy(['identifier' => $data['id']]);
        if (!$partner) {
            return $this->partnerFactory->create();
        }

        return $partner;
    }

    /**
     *
     * @param Partner $partner
     *
     * @return Partner
     */
    private function setUser(Partner $partner)
    {
        $channelManager = $partner->getChannelManager();
        $user = $partner->getUser();

        if (!$user) {
            $user = $this->cmUserRepository->findOneBy(['username' => $partner->getIdentifier()]);
        }

        if (!$user) {
            $user = new CmUser();
            $user
                ->setUsername($partner->getIdentifier())
                ->setChannelManager($channelManager);
        }

        if ($partner->getChannelManagerHubApiKey()) {
            $password = $this->passwordEncoder->encodePassword($user, $partner->getChannelManagerHubApiKey());
            $user->setPassword($password);

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return $partner->setUser($user->setChannelManager($channelManager));
    }
}
