<?php

namespace Dev\EncryptBundle\Service;

class Logger
{
    /**
     * @var \Monolog\Logger $logger
     */
    protected $logger;

    /**
     * @var \Monolog\Logger $workerLogger
     */
    protected $workerLogger;

    /**
     * @return \Monolog\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param \Monolog\Logger $logger
     * @return \Monolog\Logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return \Monolog\Logger
     */
    public function getWorkerLogger()
    {
        return $this->workerLogger;
    }

    /**
     * @param \Monolog\Logger $workerLogger
     * @return \Monolog\Logger
     */
    public function setWorkerLogger($workerLogger)
    {
        $this->workerLogger = $workerLogger;
        return $this;
    }
}
