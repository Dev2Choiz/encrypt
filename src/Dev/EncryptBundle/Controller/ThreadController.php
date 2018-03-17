<?php

namespace Dev\EncryptBundle\Controller;

use Dev\EncoderBundle\Service\EncodeManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ThreadController extends Controller
{
    public function runAction($task, $idProcess, $clientIp)
    {
        ini_set('max_execution_time', 900);
        ini_set('memory_limit', '512M');

        $data = [
            'idProcess'   => $idProcess,
            'task'   => $task,
            'mode'   => 'stream',
            'clientIp' => $clientIp
        ];

        /** @var EncodeManager $svcEncryptManager */
        $svcEncryptManager = $this->get('dev_encoder.service.encrypt_manager');
        $svcEncryptManager->getDataInFileSystem($idProcess, $task);
        $svcEncryptManager->setWithWorker(false);
        $svcEncryptManager->receiveProcess($data);

        return new Response('OK');
    }
}
