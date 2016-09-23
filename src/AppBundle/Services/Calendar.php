<?php

namespace AppBundle\Services;

use Fuz\QuickStartBundle\Base\BaseService;

class Calendar extends BaseService
{
    public function getStatus(array $events)
    {
        $time = time();

        foreach ($events as $event) {
            if ($time >= $event['start'] && $time <= $event['end']) {
                return [
                    'status' => 'full',
                    'event'  => $event,
                ];
            }
        }

        $next = null;
        foreach ($events as $event) {
            if ($event['start'] > $time) {
                $next = $event;
                break ;
            }
        }

        return [
            'status' => 'free',
            'event'  => $next,
        ];
    }

    public function getRoomsStatuses($criteria = null)
    {
        $rooms = $this->get('app.google')->listRooms($criteria);

        foreach ($rooms as $id => $data) {
            $events = $this->get('app.google')->listEvents($data['email']);

            if ($events) {
                $availability         = $this->getStatus($events);
                $data['availability'] = $availability;
            } else {
                $data['availability'] = [
                    'status' => 'free',
                    'event'  => null,
                ];
            }

            $data['score'] = 4;
            if ($data['availability']['status'] == 'free') {
                if ($data['availability']['event'] == null) {
                    $data['score'] = 1;
                }
                else if ($data['availability']['event']['start'] - time() >= 1800) {
                    $data['score'] = 2;
                }
                else {
                    $data['score'] = 3;
                }
            }

            $rooms[$id] = $data;
        }

        uasort($rooms, function($a, $b) {
            if ($a['score'] == $b['score']) {
                return strcmp(str_replace(' ', '', $a['name']), str_replace(' ', '', $b['name']));
            }

            return $a['score'] < $b['score'] ? 1 : -1;
        });

        return $rooms;
    }
}
