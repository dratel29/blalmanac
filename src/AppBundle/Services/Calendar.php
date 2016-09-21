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

            $rooms[$id] = $data;
        }

        return $rooms;
    }
}
