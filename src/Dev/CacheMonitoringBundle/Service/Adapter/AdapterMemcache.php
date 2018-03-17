<?php

namespace Dev\CacheMonitoringBundle\Service\Adapter;

class AdapterMemcache extends \Memcache implements AdapterCacheInterface
{
    public function getMultiple(array $keys)
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
        }
        //$result = $this->get($keys);

        return $result;
    }
    public function getStatistics()
    {
        return $this->getStats() ? : [];
    }

    public function getAllKeys()
    {
        return $this->getAllMemcacheKeys();
    }


    public function getAllMemcacheKeys($limit = null)
    {
        $slabs = $this->getExtendedStats('slabs');
        $keysCache = [];
        foreach ($slabs as $serverSlabs) {
            foreach ($serverSlabs as $slabId => $slabMeta) {
                try {
                    $cacheDump = $this->getExtendedStats('cachedump', (int) $slabId, 1000);
                } catch (\Exception $e) {
                    continue;
                }

                if (!is_array($cacheDump)) {
                    continue;
                }

                foreach ($cacheDump as $dump) {
                    if (!is_array($dump)) {
                        continue;
                    }
                    foreach ($dump as $key => $value) {
                        $keysCache[] = $key;

                        if (count($keysCache) === $limit) {
                            return $keysCache;
                        }
                    }
                }
            }
        }

        return $keysCache;
    }

    public function deleteByKey($keys)
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
    }
}
