<?php

namespace Dev\EncryptBundle\Service\RabbitMQ;

use Monolog\Logger;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;

abstract class Worker implements ConsumerInterface
{
    /**
     * @var Logger $logger
     */
    protected $logger;
    
    /**
     * @return Logger worker
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param Logger $logger
     * @return Worker
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
        return $this;
    }
}
