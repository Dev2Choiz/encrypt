<?php

namespace Dev\EncryptBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Task
{
    /** @var string $labelle */
    public $labelle;

    public function __construct()
    {
    }

    /**
     * @return string
     */
    public function getLabelle(): string
    {
        return $this->labelle;
    }

    /**
     * @param string $labelle
     * @return Task
     */
    public function setLabelle(string $labelle): Task
    {
        $this->labelle = $labelle;
        return $this;
    }
}
