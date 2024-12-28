<?php

namespace Modules\ShippingPlugin\Http\Services\Gateways;

use Illuminate\Support\Facades\Http;
use Modules\ShippingPlugin\Http\Services\ShippingService;

class Aramex
{
    private object $response;
    protected string $apiKey;
    protected string $apiEndpoint;

    public function __construct(protected string $trackingNumber = '000000')
    {
        // Set up the Aramex API key and endpoint (update according to Aramex documentation)
        $this->apiKey = get_static_option('aramex_api_key') ?? '';
        $this->apiEndpoint = 'https://api.aramex.com/track/v1/shipments'; // Sample Aramex API endpoint

        // Set up the HTTP client with the required headers
        $this->response = Http::retry(3, 100)->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey, // Assuming Bearer token-based authentication
            'Content-Type' => 'application/json'
        ]);
    }

    public function track()
    {
        $return_message = [];
        try {
            $data = $this->getDetails();

            $return_message = [
                'status' => true,
                'code' => $data->status(),
                'title' => __('Shipment information found'),
                'gateway' => 'aramex',
                'details' => $data->body()
            ];

        } catch (\Exception $exception) {
            if ($exception->getCode() === 404) {
                $return_message = [
                    'status' => false,
                    'code' => $exception->getCode(),
                    'title' => __('Shipment not found'),
                    'gateway' => 'aramex'
                ];
            } else if ($exception->getCode() === 401) {
                $return_message = [
                    'status' => false,
                    'code' => $exception->getCode(),
                    'title' => __('Unauthorized'),
                    'gateway' => 'aramex'
                ];
            }
        }

        return $return_message;
    }

    private function getDetails()
    {
        // Request shipment details from the Aramex API using the tracking number
        return $this->response->get($this->apiEndpoint, [
            'trackingNumber' => $this->trackingNumber
        ]);
    }
}
