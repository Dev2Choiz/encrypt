<?php

namespace Dev\EncoderBundle\Service\DependencyInjection;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\PropertyAccess\PropertyAccess;

class DependencyInjection
{
    /** @var Container $svcContainer */
    protected $svcContainer;

    public function loadDependencies(\stdClass $object)
    {
        $this->loadParameters($object);
        $this->loadServices($object);
    }

    public function loadParameters(\stdClass $object)
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($object->getDependenceInjectionParameters() as $property => $parameterIds) {
            $parameter = $this->svcContainer->getParameter($parameterIds);
            $accessor->setValue($object, $property, $parameter);
        }
        return $this;
    }

    public function loadServices(\stdClass $object)
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($object->getDependenceInjectionServices() as $property => $serviceIds) {
            $service = $this->svcContainer->get($serviceIds);
            $accessor->setValue($object, $property, $service);
        }
        return $this;
    }

    /**
     * @return Container
     */
    public function getSvcContainer()
    {
        return $this->svcContainer;
    }

    /**
     * @param Container $svcContainer
     * @return DependencyInjection
     */
    public function setSvcContainer($svcContainer)
    {
        $this->svcContainer = $svcContainer;
        return $this;
    }
}
