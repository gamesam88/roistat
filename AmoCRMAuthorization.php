<?php

require 'vendor/autoload.php';

use AmoCRM\OAuth2\Client\Provider\AmoCRM;

class AmoCRMAuthorization
{
    private AmoCRM $provider;
    const TOKEN_FILE = './tmp/token_info.json';

    public function __construct($clientId, $clientSecret, $redirectUri)
    {
        $this->provider = new AmoCRM([
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'redirectUri' => $redirectUri,
        ]);
    }

    public function setBaseDomain($domain): void
    {
        $this->provider->setBaseDomain($domain);
    }

    public function getAuthorizationUrl(): string
    {
        $_SESSION['oauth2state'] = bin2hex(random_bytes(16));
        return $this->provider->getAuthorizationUrl(['state' => $_SESSION['oauth2state']]);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function post($url, $headers, $data): \Psr\Http\Message\ResponseInterface
    {
        return $this->provider->getHttpClient()->post($url, [
            'headers' => $headers,
            'json' => $data
        ]);
    }


    public function isAuthorised(): bool
    {
        if ($this->getToken()) {
            return true;
        }
        return false;
    }

    static public function isValidToken($token): bool
    {
        if (
            isset($token['accessToken'])
            && isset($token['refreshToken'])
            && isset($token['expires'])
        ) {
            return true;
        }
        return false;
    }

    private function getTokenData($accessToken): array
    {
        return [
            'accessToken' => $accessToken->getToken(),
            'refreshToken' => $accessToken->getRefreshToken(),
            'expires' => $accessToken->getExpires(),
        ];
    }

    public function authorize($code, $state, $redirectUri, $clientId, $clientSecret)
    {
        try {
            /** @var \League\OAuth2\Client\Token\AccessToken $access_token */
            $accessToken = $this->provider->getAccessToken(new League\OAuth2\Client\Grant\AuthorizationCode(), [
                'redirect_uri' => $redirectUri,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'grant_type' => 'authorization_code',
                'code' => $code,
                'state' => $state
            ]);

            if (!$accessToken->hasExpired()) {
                $this->saveToken($this->getTokenData($accessToken));
            }

            return $accessToken;
        } catch (League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            echo $e;
        }
    }

    public function refreshTokenIfNeeded(): void
    {
        $accessToken = $this->getToken();

        if ($accessToken->hasExpired()) {
            try {
                $accessToken = $this->provider->getAccessToken(
                    new League\OAuth2\Client\Grant\RefreshToken(),
                    ['refresh_token' => $accessToken->getRefreshToken()]
                );

                $this->saveToken($this->getTokenData($accessToken));
            } catch (League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            }
        }
    }

    private function saveToken($accessToken): void
    {
        if (self::isValidToken($accessToken)) {
            $data = [
                'accessToken' => $accessToken['accessToken'],
                'expires' => $accessToken['expires'],
                'refreshToken' => $accessToken['refreshToken'],
            ];

            file_put_contents(self::TOKEN_FILE, json_encode($data));
        } else {
            exit('Invalid access token ' . var_export($accessToken, true));
        }
    }

    public function getToken()
    {
        $accessToken = json_decode(file_get_contents(self::TOKEN_FILE), true);

        if (self::isValidToken($accessToken)) {
            return new \League\OAuth2\Client\Token\AccessToken([
                'access_token' => $accessToken['accessToken'],
                'refresh_token' => $accessToken['refreshToken'],
                'expires' => $accessToken['expires'],
            ]);
        } else {
            exit('Invalid access token ' . var_export($accessToken, true));
        }
    }
}
