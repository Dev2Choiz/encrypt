<?php

namespace Dev\EncoderBundle\Service;

class FileReader extends \SplFileObject
{
    /**
     * @param int $nbBytes
     * @return \Generator
     * @throws \Exception
     */
    public function readFile($nbBytes, $rewind = true)
    {
        if (! $this->isFile()) {
            throw new \Exception('Le fichier n\existe pas.');
        }

        if ($rewind) {
            $this->rewind();
        }
        while ($part = $this->fread($nbBytes)) {
            yield $part;
        }
    }

    public function readAllFile($rewind = true)
    {
        if (! $this->isFile()) {
            throw new \Exception('Le fichier n\existe pas.');
        }

        if ($rewind) {
            $this->rewind();
        }
        return $this->fread($this->getSize());
    }

    public function readLine()
    {
        if (! $this->isFile()) {
            throw new \Exception('Le fichier n\existe pas.');
        }

        $line = $this->current();
        $this->next();
        return $line;
    }

    /**
     * @param string $fileName
     * @return FileReader
     */
    public function setFile($fileName)
    {
        $this->__construct($fileName);
        return $this;
    }

    public static function factory($fileName)
    {
        if (! file_exists($fileName)) {
            return null;
        }

        return new self($fileName);
    }
}
