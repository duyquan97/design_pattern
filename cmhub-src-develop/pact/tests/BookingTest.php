<?php

namespace App\Pact\Tests;

use GuzzleHttp\Client;

class BookingTest
{
    private $client;
    private $id;
    private $bookingID;
    private $body;
    private $request;

    public function __construct($body, $bb8ApiUrl, $request)
    {
        $this->body = $body;
        $this->client = new Client([
            'base_uri' => $bb8ApiUrl
        ]);
        $this->request = $request;
        $this->Load();
    }

    public function Load()
    {
        // Make the real API request against the Mock Server.

        $options = [
            'json' => $this->body,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic Y21odWI6YWRtaW4xMjM=']
        ];
        if($this->request == 'POST')
            $response = $this->client->post("/api/external/bookings", $options);
        else
            $response = $this->client->put("/api/external/bookings", $options);

        if ($response->getStatusCode() != 200) {
            return false;
        }

        $json = $response->getBody();
        $data = json_decode($json);
        $this->id = $data->success[0]->id;
        $this->bookingID = $data->success[0]->externalId;

        return $this->bookingID;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getBookingId()
    {
        return $this->bookingID;
    }
}

