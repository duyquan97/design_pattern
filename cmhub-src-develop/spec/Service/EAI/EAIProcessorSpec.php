<?php

namespace spec\App\Service\EAI;

use App\Entity\Transaction;
use App\Model\ProductAvailabilityCollectionInterface;
use App\Model\ProductRateCollectionInterface;
use App\Service\EAI\EAIClient;
use App\Service\EAI\EAIProcessor;
use App\Service\EAI\EAIResponse;
use App\Service\EAI\Serializer\EAISerializer;
use PhpSpec\ObjectBehavior;

class EAIProcessorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EAIProcessor::class);
    }

    function let(EAIClient $client, EAISerializer $serializer)
    {
        $this->beConstructedWith($client, $serializer);
    }

    function it_updates_availability_against_eai(
        EAIClient $client,
        EAISerializer $serializer,
        EAIResponse $response,
        ProductAvailabilityCollectionInterface $availabilityCollection
    )
    {
        $serializer
            ->normalize($availabilityCollection)
            ->willReturn($data = ['pe' => 'pito']);

        $client->request('/send/channel_room_availability', $data)->willReturn($response);
        $this->updateAvailabilities($availabilityCollection)->shouldBe($response);
    }


    function it_updates_room_price_against_eai(
        EAIClient $client,
        EAISerializer $serializer,
        EAIResponse $response,
        ProductRateCollectionInterface $productRateCollection,
        Transaction $transaction
    )
    {
        $serializer
            ->normalize($productRateCollection)
            ->willReturn($data = ['pe' => 'pito']);

        $client->request('/send/channel_room_price', $data)->willReturn($response);

        $this->updateRates($productRateCollection, $transaction)->shouldBe($response);
    }
}
