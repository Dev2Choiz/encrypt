<?php

namespace Dev\EncryptBundle\Composer;

use \Composer\Script\Event;

class EncryptInit
{
    public static function execute(Event $event)
    {
        static::createFolders($event);
    }

    private static function createFolders(Event $event)
    {
        $baseDir = $event->getComposer()->getConfig()->get('vendor-dir') . '/..';
        $paths = [
            $baseDir . '/FILES',
            $baseDir . '/FILES/sources',
            $baseDir . '/FILES/targets',
        ];

        foreach ($paths as $path) {
            if (!file_exists($path)) {
                $event->getIO()->write("Creation du dossier $path.", true, \Composer\IO\IOInterface::NORMAL);
                mkdir($path);
            } else {
                $event->getIO()->write("Le dossier $path existe deja.", true, \Composer\IO\IOInterface::NORMAL);
            }
        }
    }
}
