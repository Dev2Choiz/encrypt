<?php

namespace Dev\EncryptBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("METHOD")
 */
class JsConfig
{
    /** @var  string $method */
    public $method;

    /**
     * @return string
     */
    public function getMethod (): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return JsConfig
     */
    public function setMethod (string $method): JsConfig
    {
        $this->method = $method;
        return $this;
    }
}
