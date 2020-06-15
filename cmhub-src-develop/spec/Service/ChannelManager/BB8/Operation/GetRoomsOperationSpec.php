<?php

namespace spec\App\Service\ChannelManager\BB8\Operation;

use App\Entity\Partner;
use App\Entity\Product;
use App\Exception\FormValidationException;
use App\Exception\PartnerNotFoundException;
use App\Form\Bb8GetRoomsType;
use App\Model\ProductCollection;
use App\Service\ChannelManager\BB8\Operation\GetRoomsOperation;
use App\Service\ChannelManager\BB8\Operation\Model\GetRooms;
use App\Service\ChannelManager\BB8\Serializer\ProductCollectionNormalizer;
use App\Service\Loader\PartnerLoader;
use App\Service\Loader\ProductLoader;
use App\Utils\FormHelper;
use App\Utils\Monolog\CmhubLogger;
use App\Utils\Monolog\LogAction;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class GetRoomsOperationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(GetRoomsOperation::class);
    }

    function let(
        PartnerLoader $partnerLoader,
        ProductLoader $productLoader,
        ProductCollectionNormalizer $productCollectionNormalizer,
        FormFactoryInterface $formFactory,
        CmhubLogger $logger,
        FormHelper $formHelper
    )
    {
        $this->beConstructedWith(
            $partnerLoader,
            $productLoader,
            $productCollectionNormalizer,
            $formFactory,
            $logger,
            $formHelper
        );

    }

    function it_handle_success_with_partner_ids_and_update_date(
        Partner $partner,
        Partner $partner1,
        ProductLoader $productLoader,
        CmhubLogger $logger,
        Request $request,
        ParameterBag $parameterBag,
        FormFactoryInterface $formFactory,
        Form $form,
        GetRooms $rooms,
        ProductCollection $productCollection,
        ProductCollectionNormalizer $productCollectionNormalizer
    )
    {
        $updateDate = new \DateTime('2019-12-20T20:12:39.123Z');
        $dataRequest = [
            'externalPartnerIds' => '00019158,00019160',
            'externalUpdatedFrom' => '2019-12-20T20:12:39.123Z',
        ];
        $request->query = $parameterBag;
        $parameterBag->all()->willReturn($dataRequest);
        $formFactory->create(Bb8GetRoomsType::class, Argument::any())->willReturn($form);
        $form->submit(Argument::any(), true)->shouldBeCalled();
        $form->isValid()->willReturn(true);
        $form->getData()->shouldBeCalled()->willReturn($rooms);
        $rooms->getPartners()->willReturn([$partner, $partner1]);
        $rooms->getExternalUpdatedFrom()->willReturn($updateDate);
        $productLoader->getByUpdatedDate($updateDate, [$partner, $partner1])->willReturn($productCollection);

        $productCollectionNormalizer->normalize(Argument::type(ProductCollection::class))->willReturn($normalizeData = ['normalized' => 'data']);

        $logger->addOperationInfo(LogAction::GET_PRODUCTS, null, $this)->shouldBeCalled();

        $this->handle($request)->shouldBe($normalizeData);
    }

    function it_handle_success_with_partner_ids(
        Partner $partner,
        Partner $partner1,
        ProductLoader $productLoader,
        CmhubLogger $logger,
        ProductCollection $productCollection,
        Request $request,
        ParameterBag $parameterBag,
        FormFactoryInterface $formFactory,
        Form $form,
        GetRooms $rooms,
        ProductCollectionNormalizer $productCollectionNormalizer
    )
    {
        $dataRequest = [
            "externalPartnerIds" => "00019158,00019160"
        ];
        $request->query = $parameterBag;
        $parameterBag->all()->willReturn($dataRequest);
        $formFactory->create(Bb8GetRoomsType::class, Argument::any())->willReturn($form);
        $form->submit(Argument::any(), true)->shouldBeCalled();
        $form->isValid()->willReturn(true);
        $form->getData()->shouldBeCalled()->willReturn($rooms);
        $rooms->getPartners()->willReturn([$partner, $partner1]);
        $rooms->getExternalUpdatedFrom()->willReturn(null);
        $productLoader->getByUpdatedDate(null, [$partner, $partner1])->willReturn($productCollection);
        $productCollectionNormalizer->normalize(Argument::type(ProductCollection::class))->willReturn($normalizeData = ['normalized' => 'data']);

        $logger->addOperationInfo(LogAction::GET_PRODUCTS, null, $this)->shouldBeCalled();

        $this->handle($request)->shouldBe($normalizeData);
    }

    function it_handle_success_with_update_date(
        ProductLoader $productLoader,
        CmhubLogger $logger,
        Request $request,
        ParameterBag $parameterBag,
        FormFactoryInterface $formFactory,
        Form $form,
        GetRooms $rooms,
        ProductCollection $productCollection,
        ProductCollectionNormalizer $productCollectionNormalizer
    )
    {
        $updateDate = new \DateTime('2019-12-17T06:48:06.123Z');
        $dataRequest = [
            'externalUpdatedFrom' => '2019-12-17T06:48:06.123Z',
        ];
        $request->query = $parameterBag;
        $parameterBag->all()->willReturn($dataRequest);
        $formFactory->create(Bb8GetRoomsType::class, Argument::any())->willReturn($form);
        $form->submit(Argument::any(), true)->shouldBeCalled();
        $form->isValid()->willReturn(true);
        $form->getData()->shouldBeCalled()->willReturn($rooms);
        $rooms->getPartners()->willReturn(null);
        $rooms->getExternalUpdatedFrom()->willReturn($updateDate);
        $productLoader->getByUpdatedDate($updateDate, null)->willReturn($productCollection);
        $productCollectionNormalizer->normalize(Argument::type(ProductCollection::class))->willReturn($normalizeData = ['normalized' => 'data']);

        $logger->addOperationInfo(LogAction::GET_PRODUCTS, null, $this)->shouldBeCalled();

        $this->handle($request)->shouldBe($normalizeData);
    }

    function it_handle_fail(
        ProductLoader $productLoader,
        CmhubLogger $logger,
        Request $request,
        ParameterBag $parameterBag,
        FormFactoryInterface $formFactory,
        Form $form,
        GetRooms $rooms,
        Product $product,
        FormHelper $formHelper
    )
    {
        $dataRequest = [
            'externalUpdatedFrom' => '2019-12-17T06:48:06.123Z',
        ];
        $request->query = $parameterBag;
        $parameterBag->all()->willReturn($dataRequest);
        $formFactory->create(Bb8GetRoomsType::class, Argument::any())->willReturn($form);
        $form->submit(Argument::any(), true)->shouldBeCalled();
        $form->isValid()->willReturn(false);
        $formHelper->getErrorsFromForm($form)->willReturn(['']);
        $form->getData()->shouldNotBeCalled();
        $rooms->getPartners()->shouldNotBeCalled();
        $rooms->getExternalUpdatedFrom()->shouldNotBeCalled();
        $productLoader->getByUpdatedDate()->shouldNotBeCalled();

        $logger->addOperationInfo(LogAction::GET_PRODUCTS, null, $this)->shouldNotBeCalled();
        $this->shouldThrow(FormValidationException::class)->during('handle', [$request]);
    }

    function it_handle_fail_with_wrong_partner_ids(
        ProductLoader $productLoader,
        CmhubLogger $logger,
        Request $request,
        ParameterBag $parameterBag,
        FormFactoryInterface $formFactory,
        Form $form,
        GetRooms $rooms,
        FormHelper $formHelper
    )
    {
        $dataRequest = [
            "externalPartnerIds" => "0000000,1111111"
        ];
        $request->query = $parameterBag;
        $parameterBag->all()->willReturn($dataRequest);
        $formFactory->create(Bb8GetRoomsType::class, Argument::any())->willReturn($form);
        $form->submit(Argument::any(), true)->shouldBeCalled();
        $form->isValid()->willReturn(false);
        $formHelper->getErrorsFromForm($form)->willReturn(['']);
        $form->getData()->shouldNotBeCalled();
        $rooms->getPartners()->shouldNotBeCalled();
        $rooms->getExternalUpdatedFrom()->shouldNotBeCalled();
        $productLoader->getByUpdatedDate()->shouldNotBeCalled();

        $logger->addOperationInfo(LogAction::GET_PRODUCTS, null, $this)->shouldNotBeCalled();

        $this->shouldThrow(FormValidationException::class)->during('handle', [$request]);
    }
}
