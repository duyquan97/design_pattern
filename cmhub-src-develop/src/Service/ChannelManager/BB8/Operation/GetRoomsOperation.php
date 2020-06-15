<?php

namespace App\Service\ChannelManager\BB8\Operation;

use App\Exception\FormValidationException;
use App\Form\Bb8GetRoomsType;
use App\Model\PartnerInterface;
use App\Model\ProductCollection;
use App\Service\ChannelManager\BB8\Operation\Model\GetRooms;
use App\Service\ChannelManager\BB8\Serializer\ProductCollectionNormalizer;
use App\Service\Loader\PartnerLoader;
use App\Service\Loader\ProductLoader;
use App\Utils\FormHelper;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class GetRoomsOperation
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class GetRoomsOperation implements BB8OperationInterface
{
    public const NAME = 'get_rooms';

    /**
     *
     * @var PartnerLoader
     */
    private $partnerLoader;

    /**
     *
     * @var ProductLoader
     */
    private $productLoader;

    /**
     *
     * @var ProductCollectionNormalizer
     */
    private $productCollectionNormalizer;

    /**
     *
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     *
     * @var CmhubLogger
     */
    private $logger;

    /**
     *
     * @var FormHelper
     */
    private $formHelper;

    /**
     *
     * GetRoomsOperation constructor.
     *
     * @param PartnerLoader               $partnerLoader
     * @param ProductLoader               $productLoader
     * @param ProductCollectionNormalizer $productCollectionNormalizer
     * @param FormFactoryInterface        $formFactory
     * @param CmhubLogger                 $logger
     * @param FormHelper                  $formHelper
     */
    public function __construct(PartnerLoader $partnerLoader, ProductLoader $productLoader, ProductCollectionNormalizer $productCollectionNormalizer, FormFactoryInterface $formFactory, CmhubLogger $logger, FormHelper $formHelper)
    {
        $this->partnerLoader = $partnerLoader;
        $this->productLoader = $productLoader;
        $this->productCollectionNormalizer = $productCollectionNormalizer;
        $this->formFactory = $formFactory;
        $this->logger = $logger;
        $this->formHelper = $formHelper;
    }

    /**
     * @param Request $request
     *
     * @return array
     *
     * @throws FormValidationException
     */
    public function handle(Request $request): array
    {
        $data = $request->query->all();

        $form = $this->formFactory->create(Bb8GetRoomsType::class);
        $form->submit($data, true);

        if (!$form->isValid()) {
            $errors = $this->formHelper->getErrorsFromForm($form);
            throw new FormValidationException($errors);
        }

        $getRooms = $form->getData();

        $productCollection = $this->productLoader->getByUpdatedDate($getRooms->getExternalUpdatedFrom(), $getRooms->getPartners());

        $this->logger->addOperationInfo(LogAction::GET_PRODUCTS, null, $this);

        return $this->productCollectionNormalizer->normalize($productCollection);
    }

    /**
     *
     * @param string $operation
     *
     * @return bool
     */
    public function supports(string $operation): bool
    {
        return self::NAME === $operation;
    }
}
