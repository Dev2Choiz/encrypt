<?php

namespace Dev\EncoderBundle\Service;

use Dev\CacheMonitoringBundle\Service\Adapter\AdapterMemcached;
use Dev\EncryptBundle\Service\Logger;
use Dev\EncryptBundle\Service\RabbitMQ\EncodeProducer;
use Dev\EncryptBundle\Service\TaskTypeHandler\TaskTypeHandlerAbstract;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EncodeManager
{
    const SIZE_BUFFER = 10000;

    /** @var  array $data */
    protected $data;
    /** @var  string $pathSources */
    protected $pathSources;
    /** @var  string $pathTargets */
    protected $pathTargets;
    /** @var Container $svcContainer */
    protected $svcContainer;
    /** @var  \Predis\Client $svcRedis */
    protected $svcRedis;
    /** @var  EncodeProducer $encodeProducer */
    protected $encodeProducer;
    /** @var AdapterMemcached $svcMemcached */
    protected $svcMemcached;
    /** @var  bool $checkIp */
    protected $checkIp = true;
    /** @var boolean $withWorker */
    protected $withWorker = true;
    /** @var array $status */
    protected $status = false;
    /** @var  Logger $logger */
    protected $logger;
    /** @var  TaskTypeHandlerAbstract $taskTypeHandler */
    private $taskTypeHandler;

    public function sendProcess($idProcess, $task, $request)
    {
        $params = array_replace_recursive($request->request->all(), $request->files->all())[$idProcess];
        $this->saveDataInFileSystem($idProcess, $task, $params);

        //premier enregistrement avec la config
        $this->redisSet($idProcess, Response::HTTP_CONTINUE, 0, [
            'clientIp' => Request::createFromGlobals()->getClientIp()
        ]);
        $this->encodeProducer->sendEncodeMsg($idProcess, $task);

        return $idProcess;
    }

    public function receiveProcess($data)
    {
        $data['originalName'] = $data['idProcess'];
        $this->setData($data);
        $idProcess = $this->data['idProcess'];

        $this->getDataInFileSystem($idProcess, $this->data['task']);

        $this->logger->getWorkerLogger()->addInfo("$idProcess : debut de la tâche.");

        $clientIp = $this->data['clientIp'];

        $this->taskTypeHandler->execute(
            function () use ($idProcess, $clientIp) {
                static $count = 0;

                // maj du status
                $progress = $this->taskTypeHandler->getProgression();
                $plus = [];

                if ($this->isWithWorker()) {
                    $plus ['clientIp'] = $clientIp;
                    $this->redisSet($idProcess, Response::HTTP_PROCESSING, $progress, $plus);

                } else {
                    $this->status = [
                            'status' => Response::HTTP_PROCESSING,
                            'progress' => $progress,
                        ] + $plus;
                    /** @var AdapterMemcached $svcCache */
                    $svcCache = $this->getSvcMemcached();
                    $svcCache->set($idProcess, $this->status, 1200);
                }

                $count++;
                return;
            }
        );

        $plus = [
            'clientIp' => $clientIp
        ];

        if ($this->isWithWorker()) {
            $this->redisSet($idProcess, Response::HTTP_OK, 100, $plus, 300);
        } else {
            $this->status = [
                'status' => Response::HTTP_OK,
                'progress' => 100
            ];

            /** @var AdapterMemcached $svcCache */
            $svcCache = $this->getSvcMemcached();
            $svcCache->set($idProcess, $this->status, 1200);
        }
        $this->logger->getWorkerLogger()->addInfo("$idProcess : fin de la tâche.");
    }

    public function status($idProcess)
    {
        if (! $this->getSvcRedis()->exists($idProcess)) {
            $this->logger->getWorkerLogger()->addWarning(getmypid() . " : La clé $idProcess n'existe pas.");
            $this->logger->getWorkerLogger()->addWarning(getmypid() . $idProcess);
            $data = [
                'status'   => Response::HTTP_NOT_FOUND,
                'progress' => 0
            ];
        } else {
            $data = $this->getSvcRedis()->get($idProcess);
            $data = json_decode($data, true);
            if (! $this->checkIp($data['clientIp'])) {
                return [
                    'status'  => Response::HTTP_UNAUTHORIZED,
                    'message' => 'Votre adresse ip ne correspond pas à l\'ip proprietaire.'
                ];
            }
        }

        return $data;
    }

    /**
     * @param $clientIpInitiallySaved
     * @return bool
     */
    public function checkIp($clientIpInitiallySaved)
    {
        if (! $this->isCheckIp()) {
            return true;
        }

        /** @var \Symfony\Component\HttpFoundation\Request $request */
        $request = $this->getSvcContainer()->get('request_stack')->getCurrentRequest();
        $currentClientIp = $request->getClientIp();

        return $currentClientIp === $clientIpInitiallySaved;
    }

    public function redisSet($key, $status, $progress, $otherData = [], $expire = null)
    {
        $data = array_merge(array('status' => $status, 'progress' => $progress), $otherData);

        $this->logger->getWorkerLogger()->addInfo($key);
        $this->logger->getWorkerLogger()->addInfo(json_encode($data));
        $this->logger->getWorkerLogger()->addInfo(PHP_EOL . PHP_EOL . PHP_EOL);
        $this->getSvcRedis()->set($key, json_encode($data));
        if (null !== $expire) {
            $this->getSvcRedis()->expire($key, $expire);
        }
    }

    public function generatePathSource($idProcess)
    {
        return $this->getPathSources() . DIRECTORY_SEPARATOR . $idProcess;
    }

    public function generatePathTarget($idProcess)
    {
        return $this->getPathTargets() . DIRECTORY_SEPARATOR . $idProcess;
    }

    public function saveDataInFileSystem($idProcess, $task, $params)
    {
        $class = "Dev\EncryptBundle\Service\TaskTypeHandler\\{$task}Handler";
        $this->taskTypeHandler = new $class(
            $idProcess,
            $this->generatePathSource($idProcess),
            $this->generatePathTarget($idProcess)
        );
        $this->taskTypeHandler->saveInFileSystem($params);
    }

    public function getDataInFileSystem($idProcess, $task)
    {
        $class = "Dev\EncryptBundle\Service\TaskTypeHandler\\{$task}Handler";
        $this->taskTypeHandler = new $class(
            $idProcess,
            $this->generatePathSource($idProcess),
            $this->generatePathTarget($idProcess)
        );
        $this->taskTypeHandler->getInFileSystem();
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return string
     */
    public function getPathSources()
    {
        return $this->pathSources;
    }

    /**
     * @param string $pathSources
     * @return EncodeManager
     */
    public function setPathSources($pathSources)
    {
        $this->pathSources = $pathSources;
        return $this;
    }

    /**
     * @return string
     */
    public function getPathTargets()
    {
        return $this->pathTargets;
    }

    /**
     * @param string $pathTargets
     * @return EncodeManager
     */
    public function setPathTargets($pathTargets)
    {
        $this->pathTargets = $pathTargets;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSvcContainer()
    {
        return $this->svcContainer;
    }

    /**
     * @param Container $svcContainer
     * @return EncodeManager
     */
    public function setSvcContainer($svcContainer)
    {
        $this->svcContainer = $svcContainer;
        return $this;
    }

    /**
     * @return EncodeProducer
     */
    public function getEncodeProducer()
    {
        return $this->encodeProducer;
    }

    /**
     * @param EncodeProducer $encodeProducer
     * @return EncodeManager
     */
    public function setEncodeProducer($encodeProducer)
    {
        $this->encodeProducer = $encodeProducer;
        return $this;
    }

    /**
     * @return \Predis\Client
     */
    public function getSvcRedis()
    {
        return $this->svcRedis;
    }

    /**
     * @param \Predis\Client $svcRedis
     * @return EncodeManager
     */
    public function setSvcRedis($svcRedis)
    {
        $this->svcRedis = $svcRedis;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isCheckIp()
    {
        return $this->checkIp;
    }

    /**
     * @param boolean $checkIp
     * @return EncodeManager
     */
    public function setCheckIp($checkIp)
    {
        $this->checkIp = $checkIp;
        return $this;
    }

    /**
     * @return bool
     */
    public function isWithWorker()
    {
        return $this->withWorker;
    }

    /**
     * @param bool $withWorker
     * @return EncodeManager
     */
    public function setWithWorker($withWorker)
    {
        $this->withWorker = $withWorker;
        return $this;
    }

    /**
     * @return AdapterMemcached
     */
    public function getSvcMemcached()
    {
        return $this->svcMemcached;
    }

    /**
     * @param AdapterMemcached $svcMemcached
     * @return EncodeManager
     */
    public function setSvcMemcached($svcMemcached)
    {
        $this->svcMemcached = $svcMemcached;
        return $this;
    }

    /**
     * @return array
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param array $status
     * @return EncodeManager
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return \Symfony\Bridge\Monolog\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param \Symfony\Bridge\Monolog\Logger $logger
     * @return EncodeManager
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
        return $this;
    }
}
