<?php

namespace Dev\EncryptBundle\Service\RabbitMQ;

use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Component\HttpFoundation\Request;

class EncodeProducer extends Producer
{
    protected $connection;

    public function sendEncodeMsg($idProcess, $task)
    {
        $data = array(
            'idProcess' => $idProcess,
            'task'      => $task,
            'clientIp'  => Request::createFromGlobals()->getClientIp()
        );
        $this->setContentType('application/json');
        $this->publish(json_encode($data));

        return true;
    }
}
