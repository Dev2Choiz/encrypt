<?php

namespace Dev\EncoderBundle\Service;

class FileWriter extends \SplFileObject
{
    const OPEN_MODE = 'w';

    public function writeFile($text, $offset = 0)
    {
        if (! $this->isWritable()) {
            return null;
        }
        $this->fseek($offset, SEEK_END);
        return $this->fwrite($text);
    }

    /**
     * @param string $fileName
     * @return FileReader
     */
    public function setFile($fileName)
    {
        return new self($fileName, self::OPEN_MODE);
    }

    public static function factory($fileName)
    {
        if (! file_exists($fileName) || is_writable($fileName)) {
            $fOpen = fopen($fileName, self::OPEN_MODE);
            fclose($fOpen);
        }

        return new self($fileName, self::OPEN_MODE);
    }
}
