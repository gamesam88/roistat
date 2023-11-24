<?php

require 'vendor/autoload.php';
require 'AmoCRMAuthorization.php';

class AmoCRMService
{
    private AmoCRMAuthorization $authorization;
    const BASE_URL = 'https://dyxpp.amocrm.ru';

    public function __construct($clientId, $clientSecret, $redirectUri)
    {
        $this->authorization = new AmoCRMAuthorization($clientId, $clientSecret, $redirectUri);
    }

    /**
     * @return AmoCRMAuthorization
     */
    public function getAuthorization(): AmoCRMAuthorization
    {
        return $this->authorization;
    }

    public function createLeadWithContact($formData): void
    {
        $accessToken = $this->authorization->getToken();
        $url = self::BASE_URL . '/api/v4/leads/complex';

        $headers = [
            'Authorization' => 'Bearer ' . $accessToken->getToken(),
            'Content-Type' => 'application/json',
        ];

        try {
            $response = $this->authorization->post($url, $headers, $formData);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            echo $e;
        }
    }
}
