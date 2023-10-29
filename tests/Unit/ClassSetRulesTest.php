<?php

declare(strict_types=1);

namespace Modulith\ArchCheck\Test\Unit;

use Modulith\ArchCheck\ClassSet;
use Modulith\ArchCheck\ClassSetRules;
use Modulith\ArchCheck\Expression\ForClasses\HaveNameMatching;
use Modulith\ArchCheck\Expression\ForClasses\Implement;
use Modulith\ArchCheck\Expression\ForClasses\ResideInOneOfTheseNamespaces;
use Modulith\ArchCheck\Rules\Rule;
use PHPUnit\Framework\TestCase;

class ClassSetRulesTest extends TestCase
{
    public function test_create_class_set_rules_correctly(): void
    {
        $classSet = ClassSet::fromDir(__DIR__.'/../E2E/fixtures/happy_island');

        $rule_1 = Rule::allClasses()
            ->that(new ResideInOneOfTheseNamespaces('App\Controller'))
            ->should(new Implement('ContainerAwareInterface'))
            ->because('all controllers should be container aware');

        $rule_2 = Rule::allClasses()
            ->that(new ResideInOneOfTheseNamespaces('App\Controller'))
            ->should(new HaveNameMatching('*Controller'))
            ->because('we want uniform naming');

        $rules = [$rule_1, $rule_2];

        $classSetRules = ClassSetRules::create($classSet, ...$rules);

        $this->assertEquals($classSet, $classSetRules->getClassSet());
        $this->assertEquals($rules, $classSetRules->getRules());
    }
}
