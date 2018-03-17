<?php

namespace Dev\EncryptBundle\Service\TaskTypeHandler;

use Dev\EncoderBundle\Service\FileReader;
use Dev\EncoderBundle\Service\FileWriter;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Stopwatch\Stopwatch;

class NumericBaseHandler extends TaskTypeHandlerAbstract
{
    /** @var  int $currentBase */
    protected $currentBase;
    /** @var  int $wishedBase */
    protected $wishedBase;
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

        $wishedBaseSize = $this->sizeTreatedChar($this->wishedBase);
        $currentBaseSize = $this->sizeTreatedChar($this->currentBase);
        $pattern = '%0' . $wishedBaseSize . 's';

        foreach ($this->content as $key => $source) {
            $result = '';
            for ($i = 0; $i < strlen($source); $i += $currentBaseSize) {
                $piece = substr($source, $i, $currentBaseSize);
                if ('ASCII' === $this->currentBase) {
                    $piece = ord($piece);
                }

                $val = base_convert(
                    $piece,
                    ('ASCII' === $this->currentBase) ? 10 : $this->currentBase,
                    ('ASCII' === $this->wishedBase) ? 10 : $this->wishedBase
                );
                if ('ASCII' === $this->wishedBase) {
                    $val = chr((int) $val);
                }
                $result .= sprintf($pattern, $val);
            }
            $writer->writeFile($result);

            $this->progression = (($key + 1) * strlen($source)) / $this->getSizeOriginalFile() * 100;
            if (is_callable($callable)) {
                $callable($result);
            }
        }

        $chrono->stop('timer');
        $event = $chrono->getEvent('timer');
        dump($event->getDuration() / 1000);
        dump($event);
        return true;
    }

    public function sizeTreatedChar($base)
    {
        if ('ASCII' === $base) {
            return 1;
        }

        $value = base_convert(255, 10, $base);
        return strlen((string) $value);
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
        $writer->writeFile($params['currentBase'] . PHP_EOL);
        $writer->writeFile($params['whishedBase'] . PHP_EOL);

        if ($params['text'] instanceof UploadedFile) {
            $params['text'] = file_get_contents($params['text']->getPathname());
        }
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
        $this->setCurrentBase(substr($reader->readLine(), 0, -1));
        $this->setWishedBase(substr($reader->readLine(), 0, -1));
        $this->content = $reader->readFile($this->sizeBuffer, false);
        $reader = null;

        return true;
    }

    /**
     * @return int
     */
    public function getCurrentBase()
    {
        return $this->currentBase;
    }

    /**
     * @param int $currentBase
     * @return NumericBaseHandler
     */
    public function setCurrentBase($currentBase)
    {
        $this->currentBase = $currentBase;
        return $this;
    }

    /**
     * @return int
     */
    public function getWishedBase()
    {
        return $this->wishedBase;
    }

    /**
     * @param int $wishedBase
     * @return NumericBaseHandler
     */
    public function setWishedBase($wishedBase)
    {
        $this->wishedBase = $wishedBase;
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
