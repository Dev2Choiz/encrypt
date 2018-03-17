<?php

namespace Dev\EncryptBundle\Listener;

use Dev\EncryptBundle\Annotation\PreExecutable;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class ControllerPreExecutableListener
{
    /** @var  ContainerInterface $svcContainer */
    protected $svcContainer;

    public function onKernelController(FilterControllerEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $controller = $event->getController();
        if (! isset($controller[0])) {
            return;
        }

        $controller = $controller[0];
        $annoReader = new AnnotationReader();
        $reflController = new \ReflectionClass($controller);
        $annotation = $annoReader->getClassAnnotation($reflController, PreExecutable::class);
        // Execute la methode preExecute si le controleur contient l'annotation PreExecutable
        $annotation && $controller->preExecute();
    }

    /**
     * @return ContainerInterface
     */
    public function getSvcContainer (): ContainerInterface
    {
        return $this->svcContainer;
    }

    /**
     * @param ContainerInterface $svcContainer
     * @return ControllerPreExecutableListener
     */
    public function setSvcContainer (ContainerInterface $svcContainer): ControllerPreExecutableListener
    {
        $this->svcContainer = $svcContainer;
        return $this;
    }
}
