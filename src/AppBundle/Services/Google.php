<?php

namespace AppBundle\Services;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Fuz\QuickStartBundle\Base\BaseService;

class Google extends BaseService
{
    protected $guzzle;
    protected $token;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->guzzle = new \GuzzleHttp\Client();
        $symfonyToken = $tokenStorage->getToken();
        if ($symfonyToken) {
            $googleToken = $symfonyToken->getRawToken();
            $this->token = $googleToken['access_token'];
            return;
        }
    }

    public function listRooms($criteria = null)
    {
        $cache = $this->get('app.apcu')->get(sprintf('rooms|%s', $criteria));
        if ($cache) {
            return $cache;
        }

        $response = $this->guzzle->get('https://www.googleapis.com/admin/directory/v1/customer/my_customer/resources/calendars', [
            'headers' => [
                'Authorization' => 'Bearer '.$this->token,
            ],
            'query'   => [
                'maxResults' => 500,
            ],
            'proxy'   => $this->getParameter('http.proxy'),
            'timeout' => $this->getParameter('http.timeout'),
        ]);

        $json  = json_decode($response->getBody(), true);
        $rooms = [];
        foreach ($json['items'] as $item) {
            if (is_null($criteria) || false !== strpos(str_replace(' ', '', $item['resourceName']), str_replace(' ', '', $criteria))) {
                $rooms[sha1($item['resourceEmail'])] = [
                    'name'         => $item['resourceName'], // possible xss
                    'email'        => $item['resourceEmail'],
                    'availability' => null,
                ];
            }
        }

        uasort($rooms, function($a, $b) {
            return strcmp(str_replace(' ', '', $a['name']), str_replace(' ', '', $b['name']));
        });

        $this->get('app.apcu')->set(sprintf('rooms|%s', $criteria), $rooms);

        return $rooms;
    }

    public function listEvents($roomId)
    {
        $min = \DateTime::createFromFormat("H:i:s", "00:00:00")->format(\DateTime::RFC3339);
        $max = \DateTime::createFromFormat("H:i:s", "23:59:59")->format(\DateTime::RFC3339);

        try {
            $response = $this->guzzle->get('https://www.googleapis.com/calendar/v3/calendars/'.urlencode($roomId).'/events', [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->token,
                ],
                'query'   => [
                    'timeMin'      => $min,
                    'timeMax'      => $max,
                    'singleEvents' => 'true',
                    'fields'       => 'items(creator/email,description,summary,end/dateTime,start/dateTime,status)',
                ],
                'proxy'   => $this->getParameter('http.proxy'),
                'timeout' => $this->getParameter('http.timeout'),
            ]);
        } catch (\Exception $ex) {
            return false;
        }

        $json = json_decode($response->getBody(), true);

        $events = [];
        foreach ($json['items'] as $item) {
            if ('confirmed' !== $item['status'] || !isset($item['creator']) || !isset($item['start']) || !isset($item['end'])) {
                continue;
            }

            $events[] = [
                'mate'    => htmlentities($item['creator']['email']),
                'details' => htmlentities(isset($item['summary']) ? $item['summary'] : 'Event without title'),
                'start'   => strtotime($item['start']['dateTime']),
                'end'     => strtotime($item['end']['dateTime']),
            ];
        }

        usort($events, function($a, $b) {
            return $a['start'] < $b['start'] ? -1 : 1;
        });

        return $events;
    }
}
