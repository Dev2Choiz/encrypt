<?php

namespace Dev\CacheMonitoringBundle\Service\Adapter;

class AdapterMemcached extends \Memcached implements AdapterCacheInterface
{
    public function getMultiple(array $keys)
    {
        return parent::getMulti($keys);
    }
    public function getStatistics()
    {
        return parent::getStats();
    }

    public function deleteByArrayKey($keys)
    {
        $this->deleteMultiByKey('', $keys);
    }
}
