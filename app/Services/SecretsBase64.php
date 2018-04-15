<?php
namespace App\Services;

class SecretsBase64 extends Secrets
{
    /**
     * @param string $secretFilename
     * @throws \InvalidArgumentException
     * @return string
     */
    public static function get($secretFilename)
    {
        $base64Decoded = base64_decode(parent::get($secretFilename));

        return $base64Decoded;
    }
}
