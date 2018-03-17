<?php

namespace Dev\CacheMonitoringBundle\Twig\Extension;

class TypeExtension extends \Twig_Extension
{
    public function getTests()
    {
        return array(
            new \Twig_SimpleTest('array', array($this, 'arrayFilter')),
            new \Twig_SimpleTest('object', array($this, 'objectFilter')),
            new \Twig_SimpleTest('string', array($this, 'stringFilter')),
            new \Twig_SimpleTest('integer', array($this, 'integerFilter')),
        );
    }

    public function arrayFilter($variable)
    {
        return is_array($variable);
    }

    public function objectFilter($variable)
    {
        return is_object($variable);
    }

    public function stringFilter($variable)
    {
        return is_string($variable);
    }

    public function integerFilter($variable)
    {
        return is_integer($variable);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'devCacheMonitoring.extesion.type';
    }
}
