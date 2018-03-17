<?php

namespace Dev\EncryptBundle\Listener;

use Dev\EncryptBundle\Annotation\JsConfig;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ControllerJsConfigListener
{
    protected $svcContainer;
    /** @var  array $variablesCss */
    protected $variablesCss;
    /** @var  string $behindTag */
    protected $behindTag;

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $resolver = new ControllerResolver();
        try {
            $controller = $resolver->getController($event->getRequest());
        } catch (\InvalidArgumentException $e) {
            return;
        }
        if (false === $controller) {
            return;
        }
        $action = $controller[1];
        $controller = $controller[0];


        $annoReader = new AnnotationReader();
        $reflController = new \ReflectionClass($controller);
        $reflMethod = $reflController->getMethod($action);
        /** @var JsConfig $annotation */
        $annotation = $annoReader->getMethodAnnotation($reflMethod, JsConfig::class);

        if (! $annotation) {
            return;
        }

        $accessor = PropertyAccess::createPropertyAccessor();
        $accessor->setValue($controller, 'container', $this->getSvcContainer());
        $this->injectIntoContentResponse($controller, $annotation->getMethod(), $event->getResponse());
    }

    /**
     * @param Controller $controller
     * @param string $method
     * @param Response $response
     */
    public function injectIntoContentResponse(Controller $controller, $method, Response $response)
    {
        $controller->{$method}();
        $view = \Dev\EncryptBundle\Service\JsConfig::renderView();

        $content = $response->getContent();
        $content = str_replace("<{$this->behindTag}>", "<{$this->behindTag}>\n$view", $content);
        $response->setContent($content);
    }

    /**
     * @return mixed
     */
    public function getSvcContainer()
    {
        return $this->svcContainer;
    }

    /**
     * @param mixed $svcContainer
     * @return ControllerPreExecutableListener
     */
    public function setSvcContainer($svcContainer)
    {
        $this->svcContainer = $svcContainer;
        return $this;
    }

    /**
     * @return array
     */
    public function getVariablesCss (): array
    {
        return $this->variablesCss;
    }

    /**
     * @param array $variablesCss
     * @return ControllerJsConfigListener
     */
    public function setVariablesCss (array $variablesCss): ControllerJsConfigListener
    {
        $this->variablesCss = $variablesCss;
        return $this;
    }

    /**
     * @return string
     */
    public function getBehindTag (): string
    {
        return $this->behindTag;
    }

    /**
     * @param string $behindTag
     * @return ControllerJsConfigListener
     */
    public function setBehindTag (string $behindTag): ControllerJsConfigListener
    {
        $this->behindTag = $behindTag;
        return $this;
    }
}
