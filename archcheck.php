<?php

declare(strict_types=1);

use Modulith\ArchCheck\ClassSet;
use Modulith\ArchCheck\CLI\Config;
use Modulith\ArchCheck\Expression\ForClasses\Extend;
use Modulith\ArchCheck\Expression\ForClasses\Implement;
use Modulith\ArchCheck\Expression\ForClasses\ResideInOneOfTheseNamespaces;
use Modulith\ArchCheck\Rules\Rule;

return static function (Config $config): void {
    $classSet = ClassSet::fromDir(__DIR__.'/src');

    $rules = [];

    $rules[] = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('Modulith\ArchCheck\Expression\ForClasses'))
        ->should(new Implement('Modulith\ArchCheck\Expression\Expression'))
        ->because('we want that all rules for classes implement Expression class.');

    $rules[] = Rule::allClasses()
        ->that(new Extend('Symfony\Component\Console\Command\Command'))
        ->should(new ResideInOneOfTheseNamespaces('Modulith\ArchCheck\CLI\Command'))
        ->because('we want find easily all the commands');

    $config
        ->add($classSet, ...$rules);
};
