<?php

namespace Dev\EncryptBundle\Service\RabbitMQ;

use Dev\EncoderBundle\Service\EncodeManager;
use PhpAmqpLib\Message\AMQPMessage;

class EncodeConsumer extends Worker
{
    /** @var  EncodeManager $svcEncodeManager */
    protected $svcEncodeManager;
    
    public function execute(AMQPMessage $msg)
    {
        ini_set('max_execution_time', 1000);
        $data = get_object_vars(json_decode($msg->body));
        $this->svcEncodeManager->setCheckIp(false);
        $this->svcEncodeManager->receiveProcess($data);
    }

    /**
     * @return EncodeManager
     */
    public function getSvcEncodeManager()
    {
        return $this->svcEncodeManager;
    }

    /**
     * @param EncodeManager $svcEncodeManager
     * @return EncodeConsumer
     */
    public function setSvcEncodeManager($svcEncodeManager)
    {
        $this->svcEncodeManager = $svcEncodeManager;
        return $this;
    }
}
