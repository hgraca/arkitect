<?php

declare(strict_types=1);

namespace Modulith\ArchCheck\CLI;

class Version
{
    public static function get(): string
    {
        $pharPath = \Phar::running();

        if ($pharPath) {
            $content = file_get_contents("$pharPath/composer.json");
        } else {
            $archcheckRootPath = __DIR__.'/../../';
            $content = file_get_contents($archcheckRootPath.'composer.json');
        }

        $composerData = json_decode($content, true);

        return $composerData['version'] ?? 'UNKNOWN';
    }
}
