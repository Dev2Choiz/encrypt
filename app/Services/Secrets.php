<?php
namespace App\Services;

class Secrets
{
    /**
     * @param string $secretFilename
     * @throws \InvalidArgumentException
     * @return string
     */
    public static function get($secretFilename)
    {
        if (! file_exists($secretFilename)) {
            throw new \InvalidArgumentException("Le fichier $secretFilename n'existe pas.");
        }

        return file_get_contents($secretFilename);
    }
}
