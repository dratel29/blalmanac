<?php

namespace AppBundle\Twig\Extension;

class RoomExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('roomName', [$this, 'roomName']),
            new \Twig_SimpleFilter('roomCountry', [$this, 'roomCountry']),
            new \Twig_SimpleFilter('roomSeats', [$this, 'roomSeats']),
            new \Twig_SimpleFilter('roomDirection', [$this, 'roomDirection']),
        ];
    }

    public function roomName($roomName)
    {
        if ($this->roomCountry($roomName)) {
            $roomName = trim(preg_replace('/[0-9]+/', '', substr($roomName, 4)), ' -');
        }

        $tokens = explode('/', $roomName);

        return reset($tokens);
    }

    public function roomCountry($roomName)
    {
        $country = substr($roomName, 1, 2);
        $flag    = __DIR__.'/../../../Fuz/QuickStartBundle/Resources/public/img/countries/'.$country.'.png';
        if (file_exists($flag)) {
            return 'img/countries/'.$country.'.png';
        }
    }

    public function roomSeats($roomName)
    {
        $tokens = explode('/', $roomName);

        if (isset($tokens[1])) {
            return trim($tokens[1]);
        }
    }

    public function roomDirection($roomName)
    {
        $tokens = explode('/', $roomName);

        if (isset($tokens[2])) {
            return trim($tokens[2]);
        }
    }

    public function getName()
    {
        return 'room';
    }
}
