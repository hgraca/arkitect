<?php
declare(strict_types=1);

use Modulith\ArchCheck\ClassSet;
use Modulith\ArchCheck\CLI\Config;
use Modulith\ArchCheck\Expression\ForClasses\NotImplement;
use Modulith\ArchCheck\Expression\ForClasses\ResideInOneOfTheseNamespaces;
use Modulith\ArchCheck\Rules\Rule;

return static function (Config $config): void {
    $classSet = ClassSet::fromDir(__DIR__.'/parse_error');

    $rule_1 = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('App\Service'))
        ->should(new NotImplement('ContainerAwareInterface'))
        ->because('I said so');

    $config
        ->add($classSet, $rule_1);
};
