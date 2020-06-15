<?php

namespace App\Pact\Tests;

use PHPUnit\Framework\TestCase;
use PhpPact\Consumer\Model\ConsumerRequest;
use PhpPact\Consumer\Model\ProviderResponse;
use PhpPact\Consumer\Matcher\Matcher;
use PhpPact\Consumer\InteractionBuilder;
use PhpPact\Standalone\MockService\MockServerEnvConfig;

class PushBookingContractTest extends TestCase
{
    public function testConfirmBooking()
    {
        $bookingId = 'RESA-8453628';
        $date = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime("+2 week"));
        $endDate = date('Y-m-d', strtotime("+3 week"));
        $request = new ConsumerRequest();
        $request
            ->setMethod('POST')
            ->setPath('/api/external/bookings')
            ->setHeaders($headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic Y21odWI6YWRtaW4xMjM='])
            ->setBody($body = [[
                'externalId' => $bookingId,
                'dates' => [[
                    'date' => $date,
                    'externalRateBandId' => 'SBX',
                    'externalRoomId' => '1017196'
                ]],
                'bookingStatus' => 'commit',
                'bookingType' => 'instant',
                'externalPartnerId' => '00538264',
                'bookingStart' => $startDate,
                'bookingEnd' => $endDate
            ]]);

        $matcher = new Matcher();
        $response = new ProviderResponse();
        $response
            ->setStatus(200)
            ->addHeader('Content-Type', 'application/json')
            ->setBody([
                'success' => [[
                    'id' => $matcher->somethingLike('8f2ab9f4-1c30-11ea-8ca1-0242ac190002'),
                    'externalId' => $matcher->somethingLike($bookingId)
                ]]]);

        // Create a configuration that reflects the server that was started.
        $config = new MockServerEnvConfig();
        $builder = new InteractionBuilder($config);
        $builder
            ->uponReceiving('POST Booking - Confirmation booking')
            ->with($request)
            ->willRespondWith($response);

        $result = new BookingTest($body, $config->getBaseUri(), 'POST'); // Pass in the URL to the Mock Server
        $builder->verify();
        $this->assertIsString($result->getId());
        $this->assertSame($bookingId, $result->getBookingId(), "Booking ID is expected");

        return $bookingId;
    }

    /**
     * @depends testConfirmBooking
     */
    public function testCancelBooking($bookingId)
    {
        $date = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime("+2 week"));
        $endDate = date('Y-m-d', strtotime("+3 week"));
        $request = new ConsumerRequest();
        $request
            ->setMethod('PUT')
            ->setPath('/api/external/bookings')
            ->setHeaders($headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic Y21odWI6YWRtaW4xMjM='])
            ->setBody(
                $body = [
                    [
                        'externalId' => $bookingId,
                        'dates' => [
                            [
                                'date' => $date,
                                'externalRateBandId' => 'SBX',
                                'externalRoomId' => '1017196'
                            ]
                        ],
                        'bookingStatus' => 'cancel',
                        'bookingType' => 'instant',
                        'externalPartnerId' => '00538264',
                        'bookingStart' => $startDate,
                        'bookingEnd' => $endDate
                    ]
                ]
            );

        $matcher = new Matcher();
        $response = new ProviderResponse();
        $response
            ->setStatus(200)
            ->addHeader('Content-Type', 'application/json')
            ->setBody(
                [
                    'success' => [
                        [
                            'id' => $matcher->somethingLike('8f2ab9f4-1c30-11ea-8ca1-0242ac190002'),
                            'externalId' => $matcher->somethingLike($bookingId)
                        ]
                    ]
                ]
            );

        // Create a configuration that reflects the server that was started.
        $config = new MockServerEnvConfig();
        $builder = new InteractionBuilder($config);
        $builder
            ->uponReceiving('PUT Booking - Cancellation booking')
            ->with($request)
            ->willRespondWith($response);

        $result = new BookingTest($body, $config->getBaseUri(), 'PUT'); // Pass in the URL to the Mock Server
        $builder->verify();
        $this->assertIsString($result->getId());
        $this->assertSame($bookingId, $result->getBookingId(), "Booking ID is expected");
    }
}
