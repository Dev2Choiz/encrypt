<?php

namespace Dev\CacheMonitoringBundle\Controller;

use Dev\CacheMonitoringBundle\Service\Adapter\AdapterCacheInterface;
use Doctrine\Common\Cache\MemcachedCache;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class CacheController extends Controller
{
    /**
     * @Route("/cache/{cacheName}", name="dev_cache_monitoring.index")
     */
    public function indexAction($cacheName)
    {
        /*        $memcached = new \Memcached();
                $memcached->addServer('127.0.0.1', '11211');
                $cache = new MemcachedCache();
                $cache->setMemcached($memcached);

                $keys = $memcached->getAllKeys();
                $value = $cache->fetchMultiple($keys);
        */



        /** @var AdapterCacheInterface $adapterCached */
        $adapterCache = $this->get('devcachemonitoring.adapter.' . strtolower($cacheName));

        $keys = $adapterCache->getAllKeys();
        $cacheContent = $adapterCache->getMultiple(is_array($keys) ? $keys : []);
        $stats = $adapterCache->getStatistics();
        $stats = current($stats);

        return $this->render('DevCacheMonitoringBundle:Cache:index.html.twig', array(
            'cacheName' => $cacheName,
            'cacheContent' => $cacheContent,
            'stats' => $stats,

        ));
    }

    /**
     * @Route("/cache/flushAll/{cacheName}", name="dev_cache_monitoring.flushall")
     */
    public function flushAllAction($cacheName)
    {
        $memcache = $this->get('devcachemonitoring.adapter.' . strtolower($cacheName));
        $keys      = $memcache->getAllKeys();
        $memcache->deleteByArrayKey($keys);

        return $this->redirect($this->generateUrl('dev_cache_monitoring.index', array('cacheName' => $cacheName)));
    }

    public function dump($variable)
    {
        return $this->renderView('::dump.html.twig', [
            'variable' => $variable
        ]);
    }
}
