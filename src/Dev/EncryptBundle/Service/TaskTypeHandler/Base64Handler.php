<?php

namespace Dev\EncryptBundle\Service\TaskTypeHandler;

use Dev\EncoderBundle\Service\FileReader;
use Dev\EncoderBundle\Service\FileWriter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Stopwatch\Stopwatch;

class Base64Handler extends TaskTypeHandlerAbstract
{
    const ENCODE = 1;
    const DECODE = 2;

    /** @var  int $currentBase */
    protected $action;
    /** @var  \Generator $content */
    protected $content;
    /** @var  int $sizeOriginalFile */
    protected $sizeOriginalFile;
    /** @var int $sizeBuffer */
    protected $sizeBuffer = 50000;

    public function execute(callable $callable = null)
    {
        $writer = FileWriter::factory($this->pathTarget);
        $writer->ftruncate(0);
        $chrono = new Stopwatch();

        if (ob_get_level() > 0) {
            ob_end_flush();
        }

        $chrono->start('timer');

        $content = iterator_to_array($this->content);
        $content = implode('', $content);

        if (self::ENCODE === $this->action) {
            $result = base64_encode($content);
        } else {
            $result = base64_decode($content);
        }
        $writer->writeFile($result);

        $this->progression = 100;
        if (is_callable($callable)) {
            $callable($result);
        }

        $chrono->stop('timer');
        $event = $chrono->getEvent('timer');
        dump($event->getDuration() / 1000);
        dump($event);
        return true;
    }

    public function saveInFileSystem($params)
    {
        $writer = FileWriter::factory($this->pathSource);
        $ipClient = Request::createFromGlobals()->getClientIp();
        $writer->writeFile($ipClient . PHP_EOL);
        $writer->writeFile(str_replace(
            'Dev\EncryptBundle\Service\TaskTypeHandler\\',
            '',
            str_replace('Manager', '', self::class)
            . PHP_EOL
        ));
        $writer->writeFile($params['action'] . PHP_EOL);
        $writer->writeFile($params['text']);
        $writer = null;

        return true;
    }

    public function getInFileSystem()
    {
        $reader = FileReader::factory($this->pathSource);
        $this->setSizeOriginalFile($reader->getSize());
        $this->setIpClient(substr($reader->readLine(), 0, -1));
        $reader->readLine(); // skip the handler name
        $this->setAction((int) substr($reader->readLine(), 0, -1));
        $this->content = $reader->readFile($this->sizeBuffer, false);
        $reader = null;

        return true;
    }

    /**
     * @return int
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param int $action
     * @return Base64Handler
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @return \Generator
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param \Generator $content
     * @return NumericBaseHandler
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return int
     */
    public function getSizeOriginalFile()
    {
        return $this->sizeOriginalFile;
    }

    /**
     * @param int $sizeOriginalFile
     * @return NumericBaseHandler
     */
    public function setSizeOriginalFile($sizeOriginalFile)
    {
        $this->sizeOriginalFile = $sizeOriginalFile;
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
     * @return NumericBaseHandler
     */
    public function setSizeBuffer($sizeBuffer)
    {
        $this->sizeBuffer = $sizeBuffer;
        return $this;
    }
}
