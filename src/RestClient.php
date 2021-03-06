<?php

namespace NeoLikotsi\SMSPortal;

class RestClient
{
    /**
     * @var string
     */
    const HTTP_GET = 'GET';
    /**
     * @var string
     */
    const HTTP_POST = 'POST';
    /**
     * @var string
     */
    private $baseRestUri;
    private $apiToken;
    private $client;
    private $apiId;
    private $apiSecret;
    private  $testMode = false;

    /**
     * Create a new API connection
     *
     * @param string $apiToken The token found on your integration
     */
    public function __construct(string $apiId, string $apiSecret, string $baseRestUri = 'https://rest.smsportal.com/v1/')
    {
        $this->client = new \GuzzleHttp\Client;
        $this->apiId = $apiId;
        $this->apiSecret = $apiSecret;
        $this->baseRestUri = $baseRestUri;
    }

    /**
     * Syntax candy method
     *
     * @return RestClient
     */
    public function message() : self
    {
        return $this;
    }

    /**
     * get apiToken
     *
     * https://docs.smsportal.com/reference#authentication
     * @return RestClient
     */
    public function authorize() : self
    {
        $response = $this->client->request(static::HTTP_GET, $this->baseRestUri . 'Authentication', [
            'http_errors' => false,
            'headers' => ['Authorization' => 'Basic ' . base64_encode($this->apiId . ':' . $this->apiSecret)]
        ]);

        $responseData = $this->getResponse((string) $response->getBody());
        $this->apiToken = $responseData['token'];

        return $this;
    }

    /**
     * Submit API request to send SMS
     *
     * @link https://docs.smsportal.com/reference#bulkmessages
     * @param array $options
     * @return array
     */
    public function send(array $options) : array
    {
        if ($this->testMode) {
            $options = array_merge($options, [
                'sendOptions' => [
                    'testMode' => true
                    ]
                ]);
        }

        $response = $this->authorize()->client->request(static::HTTP_POST, $this->baseRestUri . 'BulkMessages', [
            'json' => $options,
            'http_errors' => false,
            'headers' => ['Authorization' => 'Bearer ' . $this->apiToken]
        ]);

        return $this->getResponse((string) $response->getBody());
    }

    /**
     * Submit API request to send SMS
     *
     * @link https://docs.smsportal.com/reference#groupmessages
     * @param array $options
     * @return array
     */
    public function sendToGroup(array $options) : array
    {
        $response = $this->authorize()->client->request(static::HTTP_POST, $this->baseRestUri . 'GroupMessages', [
            'json' => $options,
            'http_errors' => false,
            'headers' => ['Authorization' => 'Bearer ' . $this->apiToken]
        ]);

        return $this->getResponse((string) $response->getBody());
    }

    /**
     * Get sms credit balance
     *
     * @link https://docs.smsportal.com/reference#balance
     * @return string
     */
    public function balance() : string
    {
        $response = $this->authorize()->client->request(static::HTTP_GET, $this->baseRestUri . 'Balance', [
            'http_errors' => false,
            'headers' => ['Authorization' => 'Bearer ' . $this->apiToken]
        ]);
        $responseData = $this->getResponse((string) $response->getBody());
        return $responseData['balance'];
    }

    /**
     * Tranform response string to responseData
     *
     * @param string $responseBody
     * @return array
     */
    private function getResponse(string $responseBody) : array
    {
        return json_decode($responseBody, true);
    }

    /**
     * test send REST API request
     *
     * @return this
     */
    public function inTestMode($test = true) : self
    {
        $this->testMode = $test;

        return $this;
    }

    /**
     * gets testMode of instance
     *
     * @return boolean
     */
    public function getTestMode() : bool
    {
        return $this->testMode;
    }
}
