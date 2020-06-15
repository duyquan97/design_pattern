<?php

namespace App\Controller;

use App\Entity\Experience;
use App\Entity\Factory\ExperienceFactory;
use App\Form\ExperienceType;
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
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class ExperienceController
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductController
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ExperienceFactory
     */
    private $experienceFactory;

    /**
     * @var CmhubLogger
     */
    private $logger;

    /**
     * @var FormHelper
     */
    private $formHelper;


    /**
     * ProductController constructor.
     *
     * @param FormFactoryInterface   $formFactory
     * @param EntityManagerInterface $entityManager
     * @param ExperienceFactory      $experienceFactory
     * @param CmhubLogger            $logger
     * @param FormHelper             $formHelper
     */
    public function __construct(FormFactoryInterface $formFactory, EntityManagerInterface $entityManager, ExperienceFactory $experienceFactory, CmhubLogger $logger, FormHelper $formHelper)
    {
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->experienceFactory = $experienceFactory;
        $this->logger = $logger;
        $this->formHelper = $formHelper;
    }

    /**
     * Update product information
     *
     * @param Request $request
     *
     * @return array
     *
     * @Rest\View()
     */
    public function productAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $experience = $this->getExperience($data);
        $form = $this->formFactory->create(ExperienceType::class, $experience);
        $form->submit($data, false);

        if (!$form->isValid()) {
            return $this->handleInvalidForm($request, $form, LogAction::PRODUCT_FLOW);
        }

        $this->entityManager->persist($experience);
        $this->entityManager->flush();

        $this
            ->logger
            ->addRecord(
                Logger::INFO,
                'EAI API Request',
                [
                    LogKey::TYPE_KEY       => LogType::EAI,
                    LogKey::STATUS_KEY     => LogStatus::SUCCESS,
                    LogKey::ACTION_KEY     => LogAction::PRODUCT_FLOW,
                    LogKey::EXPERIENCE_KEY => $experience->toArray(),
                    LogKey::REQUEST_KEY    => $request->getContent(),
                ],
                $this
            );

        return [LogKey::STATUS_KEY => LogStatus::SUCCESS];
    }

    /**
     * @param array $data
     *
     * @return Experience
     */
    private function getExperience(array $data): Experience
    {
        if (!array_key_exists('identifier', $data)) {
            throw new BadRequestHttpException('`identifier` is mandatory');
        }

        /* @var Experience $experience */
        $experience = $this->entityManager->getRepository(Experience::class)->findOneBy(['identifier' => $data['identifier']]);
        if (!$experience) {
            return $this->experienceFactory->create($data['identifier']);
        }

        return $experience;
    }

    /**
     * @param Request       $request
     * @param FormInterface $form
     * @param string        $flow
     *
     * @return array
     */
    private function handleInvalidForm(Request $request, FormInterface $form, string $flow)
    {
        $this
            ->logger
            ->addRecord(
                Logger::INFO,
                'EAI API Request',
                [
                    LogKey::TYPE_KEY          => LogType::EAI,
                    LogKey::STATUS_KEY        => LogStatus::PRODUCT_NOT_VALID,
                    LogKey::ACTION_KEY        => $flow,
                    LogKey::VALIDATION_ERRORS => json_encode($this->formHelper->getErrorsFromForm($form)),
                    LogKey::REQUEST_KEY       => $request->getContent(),
                ],
                $this
            );

        return [
            LogKey::STATUS_KEY        => LogStatus::SKIPPED,
            LogKey::VALIDATION_ERRORS => json_encode($this->formHelper->getErrorsFromForm($form)),
        ];
    }
}
