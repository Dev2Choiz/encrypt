<?php

namespace Dev\EncryptBundle\Service\TaskTypeHandler;

use Dev\EncoderBundle\Service\FileReader;
use Dev\EncoderBundle\Service\FileWriter;

class BinaryOperationHandler extends TaskTypeHandlerAbstract
{
    /** @var  string $operator */
    protected $operator;
    /** @var  string $firstNumber */
    protected $firstNumber;
    /** @var  string $secondNumber */
    protected $secondNumber;
    /** @var  int $sizeOriginalFile */
    protected $sizeOriginalFile;
    
    protected $sizeBuffer = 100;

    public function execute(callable $callable = null)
    {
        $method = 'execute' . ucfirst($this->getOperator());
        return $this->$method($callable);
    }

    public function executeAddition(callable $callable = null)
    {
        $writer = FileWriter::factory($this->pathTarget);
        $writer->ftruncate(0);

        if (strlen($this->firstNumber) < strlen($this->secondNumber)) {
            list($this->firstNumber, $this->secondNumber) = [$this->secondNumber, $this->firstNumber];
        }

        $firstNumber  = $this->firstNumber;
        $secondNumber = $this->secondNumber;
        $result       = $totalResult = '';
        $rest         = 0;
        for ($i = strlen($this->firstNumber) - 1; $i >= 0; --$i) {
            $chiffre1 = (int)$firstNumber[strlen($firstNumber) -1];
            $chiffre2 = (int) (
                isset($secondNumber[strlen($secondNumber) -1])
                ? $secondNumber[strlen($secondNumber) -1]
                : 0
            );
            $firstNumber  = substr($firstNumber, 0, -1);
            $secondNumber = substr($secondNumber, 0, -1);
            $sum          = $chiffre1 + $chiffre2 + $rest;
            $toAdd        =  ($i > 0) ? (int)substr($sum, -1) : $sum;
            $rest         = ($sum - (int)$toAdd) / 10;

            $result            = $toAdd . $result;
            $cmpt              = strlen($this->firstNumber) - $i;
            $this->progression = floor($cmpt / strlen($this->firstNumber) * 100);

            if (0 === ($cmpt % $this->sizeBuffer) || 0 === $i) {
                $callable($result);
                $totalResult .= $result;

                $writer->fseek(0, SEEK_SET);
                $writer->fwrite($result);
                $result = '';
                //sleep(1);
            }
        }
        $writer = null;

        return true;
    }

    public function executeSoustraction(callable $callable = null)
    {
        $writer = FileWriter::factory($this->pathTarget);
        $writer->ftruncate(0);

        $firstNumber  = $this->firstNumber;
        $secondNumber = $this->secondNumber;
        $result       = $totalResult = '';
        $retenu         = 0;

        $max = max(strlen($this->firstNumber), strlen($this->secondNumber));
        for ($i = $max - 1; $i >= 0; --$i) {
            $chiffre1 = (int) (
                isset($firstNumber[strlen($firstNumber) -1])
                ? $firstNumber[strlen($firstNumber) -1]
                : null
            );
            $chiffre2 = (int) (
                isset($secondNumber[strlen($secondNumber) -1])
                ? $secondNumber[strlen($secondNumber) -1]
                : null
            );

            $chiffre1 = (null === $chiffre1) ? $retenu : $chiffre1 + $retenu;

            $firstNumber  = substr($firstNumber, 0, -1);
            $secondNumber = substr($secondNumber, 0, -1);

            if (null === $chiffre1) {
                //$rest
                $soustraction = $chiffre1 - $chiffre2;
            } elseif (null === $chiffre2) {
            } else {
                if ($chiffre1 >= $chiffre2) {
                    $soustraction = $chiffre1 - $chiffre2;
                    $retenu = 0;
                } else {
                    $lastCharfirstNumber = substr($firstNumber, -1);
                    $soustraction = (int)('1' . $chiffre1) - $chiffre2;

                    $retenu = (int)floor($soustraction / 10);
                    $soustraction = $soustraction - ($retenu * 10);
                    /* on soustrait 1 à la retenu car c'est le don du chiffre de gauche
                     qui devra dont se décrementer de 1 et s'incrémenter de la retenu */
                    $retenu = $retenu  - 1;
                }
            }
            $toAdd        =  ($i > 0) ? (int)substr($soustraction, -1) : $soustraction;
            $retenu         = ($soustraction - (int)$toAdd) / 10;

            $result            = $toAdd . $result;
            $cmpt              = strlen($this->firstNumber) - $i;
            $this->progression = floor($cmpt / strlen($this->firstNumber) * 100);

            if (0 === ($cmpt % $this->sizeBuffer) || 0 === $i) {
                $callable($result);
                $totalResult .= $result;

                $writer->fseek(0, SEEK_SET);
                $writer->fwrite($result);
                $result = '';
                //sleep(1);
            }
        }
        $writer = null;

        return true;
    }

    public function saveInFileSystem($params)
    {
        $writer = FileWriter::factory($this->pathSource);
        $writer->writeFile(str_replace(
            'Dev\EncryptBundle\Service\TaskTypeHandler\\',
            '',
            str_replace('Manager', '', self::class)
            . PHP_EOL
        ));
        $writer->writeFile($params['operator'] . PHP_EOL);
        $writer->writeFile($params['firstNumber'] . PHP_EOL);
        $writer->writeFile($params['secondNumber'] . PHP_EOL);
        $writer = null;

        return true;
    }

    public function getInFileSystem()
    {
        $reader = FileReader::factory($this->pathSource);
        $this->setSizeOriginalFile($reader->getSize());
        $reader->readLine(); // skip first param
        $this->setOperator(substr($reader->readLine(), 0, -1));
        $this->setFirstNumber(substr($reader->readLine(), 0, -1));       // todo ca peut ne pas etre une ligne; trouver quelque chose
        $this->setSecondNumber(substr($reader->readLine(), 0, -1));
        $reader = null;

        return true;
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @param string $operator
     * @return BinaryOperationHandler
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstNumber()
    {
        return $this->firstNumber;
    }

    /**
     * @param string $firstNumber
     * @return BinaryOperationHandler
     */
    public function setFirstNumber($firstNumber)
    {
        $this->firstNumber = $firstNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getSecondNumber()
    {
        return $this->secondNumber;
    }

    /**
     * @param string $secondNumber
     * @return BinaryOperationHandler
     */
    public function setSecondNumber($secondNumber)
    {
        $this->secondNumber = $secondNumber;
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
     * @return BinaryOperationHandler
     */
    public function setSizeOriginalFile($sizeOriginalFile)
    {
        $this->sizeOriginalFile = $sizeOriginalFile;
        return $this;
    }
}
