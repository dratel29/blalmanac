<?php

namespace AppBundle\Services;

class Apcu
{
    public function get($key, $default = null)
    {
        return false === ($value = apcu_fetch($key)) ? $default : $value;
    }

    public function set($key, $value, $ttl = 3600)
    {
        return apcu_store($key, $value, $ttl);
    }
}
