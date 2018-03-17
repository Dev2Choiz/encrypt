<?php

namespace Dev\EncryptBundle\Service\TaskTypeHandler;

abstract class TaskTypeHandlerAbstract
{
    /** @var  string $ipClient */
    protected $ipClient;
    /** @var  string $idProcess */
    protected $idProcess;
    /** @var  int $sizeBuffer */
    protected $sizeBuffer;
    /** @var  string $pathSource */
    protected $pathSource;
    /** @var  string $pathTarget */
    protected $pathTarget;
    /** @var  int $progression */
    protected $progression;

    public function __construct($idProcess, $pathSource, $pathTarget)
    {
        $this->setIdProcess($idProcess);
        $this->setPathSource($pathSource);
        $this->setPathTarget($pathTarget);
        $this->setProgression(0);
    }

    abstract public function execute(callable $callable);
    abstract public function saveInFileSystem($params);
    abstract public function getInFileSystem();

    /**
     * @return string
     */
    public function getIpClient(): string
    {
        return $this->ipClient;
    }

    /**
     * @param string $ipClient
     * @return $this
     */
    public function setIpClient(string $ipClient): TaskTypeHandlerAbstract
    {
        $this->ipClient = $ipClient;
        return $this;
    }

    /**
     * @return string
     */
    public function getIdProcess()
    {
        return $this->idProcess;
    }

    /**
     * @param string $idProcess
     * @return TaskTypeHandlerAbstract
     */
    public function setIdProcess($idProcess)
    {
        $this->idProcess = $idProcess;
        return $this;
    }

    /**
     * @return int
     */
    public function getSizeBuffer()
    {
        return $this->sizeBuffer;
    }

    /**
     * @param int $sizeBuffer
     * @return TaskTypeHandlerAbstract
     */
    public function setSizeBuffer($sizeBuffer)
    {
        $this->sizeBuffer = $sizeBuffer;
        return $this;
    }

    /**
     * @return string
     */
    public function getPathSource()
    {
        return $this->pathSource;
    }

    /**
     * @param string $pathSource
     * @return TaskTypeHandlerAbstract
     */
    public function setPathSource($pathSource)
    {
        $this->pathSource = $pathSource;
        return $this;
    }

    /**
     * @return string
     */
    public function getPathTarget()
    {
        return $this->pathTarget;
    }

    /**
     * @param string $pathTarget
     * @return TaskTypeHandlerAbstract
     */
    public function setPathTarget($pathTarget)
    {
        $this->pathTarget = $pathTarget;
        return $this;
    }

    /**
     * @return int
     */
    public function getProgression()
    {
        return $this->progression;
    }

    /**
     * @param int $progression
     * @return TaskTypeHandlerAbstract
     */
    public function setProgression($progression)
    {
        $this->progression = $progression;
        return $this;
    }
}
