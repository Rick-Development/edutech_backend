<?php

namespace Modules\Wallet\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class FlutterwaveService
{
    // 1. Store the token and expiry data as class properties
    protected Client $client;
    protected ?string $accessToken = null;
    protected int $tokenExpiryTime = 0;
    protected const TOKEN_URL = 'https://idp.flutterwave.com/realms/flutterwave/protocol/openid-connect/token';
    protected const REFRESH_THRESHOLD = 60; // Refresh if less than 60 seconds remain

    public function __construct()
    {
        // Initialize the base client without the Authorization header,
        // as the header will be added dynamically by the access token logic.
        $this->client = new Client([
            'base_uri' => env('FLW_ENVIRONMENT', 'staging') === 'live'
                ? 'https://f4bexperience.flutterwave.com/'
                : 'https://f4bexperience.flutterwave.com/',
        ]);
    }

    /**
     * Retrieves a valid access token, generating a new one if it is expired.
     * @throws \Exception
     */
    protected function getValidAccessToken(): string
    {
        // Check if the current token is null or about to expire
        if (is_null($this->accessToken) || (time() > ($this->tokenExpiryTime - self::REFRESH_THRESHOLD))) {
            Log::info('Flutterwave token is expired or missing. Requesting new token.');
            
            try {
                // Request a new token
                $response = $this->client->post(self::TOKEN_URL, [
                    'form_params' => [
                        'client_id' => env('FLW_CLIENT_ID_V4'),
                        'client_secret' => env('FLW_SECRET_V4'),
                        'grant_type' => 'client_credentials',
                    ],
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded',
                    ],
                ]);

                $data = json_decode($response->getBody()->getContents(), true);

                if (!isset($data['access_token'])) {
                    throw new \Exception('Failed to retrieve access token from Flutterwave IDP.');
                }

                // 2. Store the new token and its exact expiry time
                $this->accessToken = $data['access_token'];
                // Calculate the expiry time: current time + expires_in seconds
                $this->tokenExpiryTime = time() + (int) $data['expires_in'];

            } catch (RequestException $e) {
                // Guzzle exception for failed requests (e.g., 400 Bad Request)
                Log::error('Flutterwave Token Error: ' . $e->getMessage() . ' - Response: ' . $e->getResponse()?->getBody()->getContents());
                throw new \Exception('Could not retrieve Flutterwave access token.');
            } catch (\Exception $e) {
                // Other general exceptions
                Log::error('Flutterwave Token Error: ' . $e->getMessage());
                throw $e;
            }
        }

        return $this->accessToken;
    }

    /**
     * Generic method to make authenticated API requests.
     */
    protected function makeAuthenticatedRequest(string $method, string $uri, array $options = []): array
    {
        // 3. Always call getValidAccessToken() before making an API request
        $token = $this->getValidAccessToken();

        $options['headers']['Authorization'] = 'Bearer ' . $token;
        $options['headers']['Content-Type'] = 'application/json';

        try {
            $response = $this->client->request($method, $uri, $options);
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::error('Flutterwave API Error: ' . $e->getMessage() . ' - Response: ' . $e->getResponse()?->getBody()->getContents());
            throw $e;
        } catch (\Exception $e) {
            Log::error('Flutterwave API Error: ' . $e->getMessage());
            throw $e;
        }
    }

    // Example API method using the new token logic
    public function initializePayment($data)
    {
        // Call the generic authenticated request helper
        return $this->makeAuthenticatedRequest('POST', 'payments', [
            'json' => $data
        ]);
    }

    // Example API method using the new token logic
    public function verifyTransaction($transactionId)
    {
        return $this->makeAuthenticatedRequest('GET', "transactions/{$transactionId}/verify");
    }

    /**
     * Retrieves available payment methods from Flutterwave.
     */
    public function getPaymentMethods(array $params = []): array
    {
        return $this->makeAuthenticatedRequest('GET', 'payment-methods', [
            'query' => $params
        ]);
    }



//create customer
    public function createCustomer(array $data): array
    {
        return $this->makeAuthenticatedRequest('POST', 'customers', [
            'json' => $data
        ]);
    }

//create virtual Account

    public function createVirtualAccount(array $data): array
    {
        return $this->makeAuthenticatedRequest('POST', 'virtual-accounts', [
            'json' => $data
        ]);
    }


    /**
     * create  payment methods from Flutterwave.
     */
    public function createPaymentMethod(array $data): array
    {
        return $this->makeAuthenticatedRequest('POST', 'payment-methods', [
            'json' => $data
        ]);
    }

    // retrieve payment method

    public function retrievePaymentMethod($id){
       
        return $this->makeAuthenticatedRequest('GET', "payment-methods/$id", [
           
        ]); 
    }


    public function initiateTransfer(array $data): array
    {
        return $this->makeAuthenticatedRequest('POST', 'direct-transfers', [
            'json' => $data
        ]);
    }

    public function getBanks(): array
    {
        $country = 'NG';
        return $this->makeAuthenticatedRequest('GET', 'banks', [
            'query' => ['country' => $country]
        ]);
    }

    public function resolveAccount(array $data): array
    {
        return $this->makeAuthenticatedRequest('POST', 'banks/account-resolve', [
            'json' => $data
        ]);
    }

}