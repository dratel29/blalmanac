<?php

namespace AppBundle\Services;

use Fuz\QuickStartBundle\Base\BaseService;

class Google extends BaseService
{
    protected $google;
    protected $guzzle;
    protected $token;

    public function checkTokenExpiration()
    {
        $symfonyToken = $this->get('security.token_storage')->getToken();
        if (!$symfonyToken) {
            return false;
        }

        if ($this->token) {
            return true;
        }

        $googleToken = $symfonyToken->getRawToken();
        $googleToken['created'] = $symfonyToken->getCreatedAt();
        $accessToken = json_encode($googleToken);
        $this->token = $googleToken['access_token'];

        $this->google = new \Google_Client();
        $this->google->setApplicationName($this->getParameter('site_brand'));
        $this->google->setClientId($this->getParameter('google_client_id'));
        $this->google->setClientSecret($this->getParameter('google_secret'));

        $this->google->setAccessToken($accessToken);

        if ($this->google->isAccessTokenExpired()) {
            return false;
        }

        $this->guzzle = new \GuzzleHttp\Client();

        return true;
    }

    public function listRooms()
    {
        $cache = $this->get('app.apcu')->get('rooms');
        if ($cache) {
            return $cache;
        }

        $response = $this->guzzle->get('https://www.googleapis.com/admin/directory/v1/customer/my_customer/resources/calendars', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ],
            'form_params' => [
                'maxResults' => 500,
            ]
        ]);

        $json  = json_decode($response->getBody(), true);
        $rooms = [];
        foreach ($json['items'] as $item) {
            $rooms[$item['resourceEmail']] = $item['resourceName'];
        }

        return $rooms;
    }


}