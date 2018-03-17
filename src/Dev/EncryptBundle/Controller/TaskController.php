<?php

namespace Dev\EncryptBundle\Controller;

use Dev\EncoderBundle\Service\EncodeManager;
use Dev\EncryptBundle\Annotation\PreExecutable;
use Dev\EncryptBundle\Service\TaskTypeHandler\Base64Handler;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @PreExecutable()
 *
 */
class TaskController extends Controller
{
    const TIME_OUT = 300;
    const ZERO = 0;
    const MILLE = 1000;
    const BUFFER_STREAM_SIZE = 200 * self::MILLE;

    /** @var  EncodeManager $svcEncryptManager */
    protected $svcEncryptManager;
    
    public function preExecute()
    {
        $this->svcEncryptManager = $this->get('dev_encoder.service.encrypt_manager');
    }

    public function uploadAction(Request $request, $task, $idProcess, $mode)
    {
        $formUpload = $this->get('form.factory')
                        ->createNamed($idProcess, $this->getFormTypeClass($task))
                        ->handleRequest($request);

        if (! $formUpload->isValid()) {
            $errors = [];
            foreach ($formUpload->all() as $form) {
                $error = $form->getErrors()->current();
                if ($error) {
                    $errors [$form->getName()] = $form->getName() . ' : ' . $error->getMessage();
                }
            }
            $content = $this->responseStream([
                'progress' => self::ZERO,
                'status'   => Response::HTTP_NOT_FOUND,
                'preview'  => 'Formulaire invalide.' . implode('. ', $errors)
            ]);
            return new Response($content);
        }

        $memcached = null;
        ini_set('max_execution_time', self::TIME_OUT);
        if ('worker' === $mode) {
            // empile un message rabbitmq qui sera en attente de traitement
            $this->svcEncryptManager->sendProcess($idProcess, $task, $request);
        } else {
            // sauvegarde des données sur le file system
            $params = array_replace_recursive($request->request->all(), $request->files->all())[$idProcess];
            $this->svcEncryptManager->saveDataInFileSystem($idProcess, $task, $params);

            // la tache se fera en arrière plan via une commande curl qui fera appel à un webservice
            $url = $this->get('router')->generate(
                'dev_encrypt.thread.run',
                ['task' => $task, 'idProcess' => $idProcess, 'clientIp' => $request->getClientIp()]
            );
            $cmd = 'curl "' . $request->server->get('HTTP_HOST') . $url . '"';
            $process = new Process($cmd);
            $process->setTimeout(self::TIME_OUT);
            $process->start();

            $memcached = $this->get('devcachemonitoring.adapter.memcached');
            $memcached->set($idProcess, ['status' => Response::HTTP_CONTINUE, 'progress' => 0]);
        }

        /**
         * stream de la reponse sous la forme #102#010#données (#status#progression#données)
         * le traitement en arriere plan stocke le status et la progression en cache,
         * et les données seront récupérées dans le fichier qu'il générera
         */
        ob_end_clean();
        $response = new StreamedResponse();
        $response->setCallback(function () use ($idProcess, $mode, $memcached) {
            $loop = true;
            $sleep = 1000 * self::MILLE;
            $cursorPreview = 0;
            $time = time();

            while ($loop) {
                $this->isOutTime($time) && $loop = false;
                usleep($sleep);

                // recuperation du status et de la progression
                $status = ('worker' === $mode)
                    ? $this->svcEncryptManager->status($idProcess)
                    : $memcached->get($idProcess);
                if (false === $status || Response::HTTP_NOT_FOUND === $status['status']) {
                    continue;
                }

                // recuperation des données par tranche de self::BUFFER_STREAM_SIZE
                $filePath = __DIR__ . '/../../../../FILES/targets/' . $idProcess;
                $preview = $file = '';
                if (file_exists($filePath)) {
                    $file = file_get_contents($filePath);
                    $preview = substr($file, $cursorPreview, self::BUFFER_STREAM_SIZE) ?: '';
                }
                $status['preview'] = $preview;
                $cursorPreview += strlen($preview);

                // flush de la reponse
                echo $this->responseStream($status);
                flush();

                if (Response::HTTP_OK === $status['status'] && ($cursorPreview >= strlen($file))) {
                    $loop = false;
                } elseif (Response::HTTP_OK === $status['status']) {
                    $sleep = 500 * self::MILLE;
                }
            }
        });

        return $response;
    }

    /**
    * @Route(
    *     name="dev_encrypt.task.form_factory",
    *     path="/formFactory/{task}",
    *     defaults={"task": "NumericBase"}
    * )
    */
    public function formFactoryAction($task)
    {
        $idProcess = uniqid();
        $data = [];
        $faker = \Faker\Factory::create();
        if ('NumericBase' === $task) {
            $data['text'] = $faker->text(500 * self::MILLE);
            $data['currentBase'] = 'ASCII';
            $data['whishedBase'] = 2;
        } elseif ('BinaryOperation' === $task) {
            $data['firstNumber'] = str_repeat('1234567890', 1 * self::MILLE);
            $data['operator'] = 'addition';
            $data['secondNumber'] = str_repeat('1234567890', 1 * self::MILLE);
        } elseif ('Base64' === $task) {
            $data['text'] = $faker->text(100 * self::MILLE);
            $data['action'] = Base64Handler::ENCODE;
        }

        $classFormType = $this->getFormTypeClass($task);
        $form = $this->get('form.factory')->createNamed($idProcess, $classFormType, $data);

        return $this->render('DevEncryptBundle:Upload:formFactory.html.twig', array(
            'form'   => $form->createView(),
            'taskType' => lcfirst($task),
        ));
    }

    public function getFormTypeClass($task)
    {
        return "Dev\\EncryptBundle\\Form\\{$task}Type";
    }

    public function responseStream($data)
    {
        return sprintf('%1$03d', $data['status'])
        . '#' . sprintf('%1$03d', $data['progress'])
        . '#' . $data['preview'];
    }

    public function fileLogger($msg, $erase = false)
    {
        static $fileName = 'debug';

        $fileLog = __DIR__ . '/' . $fileName . '.log';
        if (! is_string($msg) || ! is_int($msg)) {
            $msg = json_encode($msg);
        }
        file_put_contents($fileLog, $msg . PHP_EOL, $erase ? null : FILE_APPEND);
        return $this;
    }

    public function isOutTime($time)
    {
        return self::TIME_OUT < ($time - time());
    }
}
