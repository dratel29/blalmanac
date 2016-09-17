<?php

namespace AppBundle\Services;

use Fuz\QuickStartBundle\Base\BaseService;

class Calendar extends BaseService
{
    public function getStatus(array $events)
    {
       // $time = time();
        $time = strtotime(sprintf('2016-09-16T%s', date("H:i:s")));

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
}