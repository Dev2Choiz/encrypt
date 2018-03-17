<?php

namespace Dev\CacheMonitoringBundle\Service\Adapter;

interface AdapterCacheInterface
{
    public function getAllKeys();
    public function getMultiple(array $keys);
    public function getStatistics();
}
