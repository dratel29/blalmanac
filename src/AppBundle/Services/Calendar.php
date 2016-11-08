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
                $status               = $this->getStatus($events);
                $availability         = $status;
                $data['availability'] = $availability;
                $data['booking']      = $this->getBookingOptions($status);
            } else {
                $data['availability'] = [
                    'status' => 'free',
                    'event'  => null,
                ];
                $data['booking'] = $this->getBookingOptions(null);
            }

            $data['score'] = 1;
            if ($data['availability']['status'] == 'free') {
                if ($data['availability']['event'] == null) {
                    $data['score'] = 4;
                }
                else if ($data['availability']['event']['start'] - time() >= 1800) {
                    $data['score'] = 3;
                }
                else {
                    $data['score'] = 2;
                }
            }

            $rooms[$id] = $data;
        }

        uasort($rooms, function($a, $b) {
            if ($a['score'] == $b['score']) {
                $name = strcmp(str_replace(' ', '', $a['name']), str_replace(' ', '', $b['name']));

                if ($a['score'] == 4) {
                    return $name;
                }

                $startA = strtotime(date("H:i", $a['availability']['event']['start']));
                $startB = strtotime(date("H:i", $b['availability']['event']['start']));

                return $startA == $startB ? $name : $startA > $startB ? 1 : -1;
            }

            return $a['score'] < $b['score'] ? 1 : -1;
        });

        return $rooms;
    }

    public function getBookingOptions(array $status = null)
    {
        if ($status && $status['status'] !== 'free') {
            return null;
        }

        $time = time();

        $start = strtotime(date('Y-m-d 00:00:00', $time + 86400));
        if (isset($status['event']['start'])) {
            $start = $status['event']['start'];
        }

        $booking = [];
        for ($i = strtotime(date('H:00', $time)); $i <= $start || $i - 1800 < $start; $i += 1800) {
            if ($i > $start) {
                $i = $start;
            }

            if ($i > $time) {

                $duration = $i - $time;
                $hours    = intval($duration / 3600);
                $mins     = intval($duration % 3600 / 60);

                $booking[] = [
                    'time'     => date('H:i', $i),
                    'duration' => ($hours ? $hours.'h ' : '').$mins.'min'.($mins > 1 ? 's' : ''),
                ];

                if (count($booking) == 6) {
                    break ;
                }
            }
        }

        return $booking ?: null;
    }
}
