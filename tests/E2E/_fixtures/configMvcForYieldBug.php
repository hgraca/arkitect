<?php
declare(strict_types=1);

use Modulith\ArchCheck\ClassSet;
use Modulith\ArchCheck\CLI\Config;
use Modulith\ArchCheck\Expression\ForClasses\HaveNameMatching;
use Modulith\ArchCheck\Expression\ForClasses\ResideInOneOfTheseNamespaces;
use Modulith\ArchCheck\Rules\Rule;

return static function (Config $config): void {
    $mvc_class_set = ClassSet::fromDir(__DIR__.'/mvc');

    $rule_1 = Rule::allClasses()
        ->except('App\Controller\BaseController')
        ->that(new ResideInOneOfTheseNamespaces('App\Controller'))
        ->should(new HaveNameMatching('*Controller'))
        ->because('all controllers should be end name with Controller');

    $config
        ->add($mvc_class_set, ...[$rule_1]);
};
