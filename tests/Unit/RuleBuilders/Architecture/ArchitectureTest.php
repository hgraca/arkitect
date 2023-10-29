<?php

declare(strict_types=1);

namespace Modulith\ArchCheck\Test\Unit\RuleBuilders\Architecture;

use Modulith\ArchCheck\ClassSet;
use Modulith\ArchCheck\CLI\Config;
use Modulith\ArchCheck\CLI\Progress\VoidProgress;
use Modulith\ArchCheck\CLI\Runner;
use Modulith\ArchCheck\CLI\TargetPhpVersion;
use Modulith\ArchCheck\Expression\Boolean\Andx;
use Modulith\ArchCheck\Expression\ForClasses\NotResideInTheseNamespaces;
use Modulith\ArchCheck\Expression\ForClasses\ResideInOneOfTheseNamespaces;
use Modulith\ArchCheck\RuleBuilders\Architecture\Architecture;
use Modulith\ArchCheck\Test\Unit\AbstractUnitTest;

class ArchitectureTest extends AbstractUnitTest
{
    public function test_it_should_see_violations_only_outside_exclusions(): void
    {
        $rules = Architecture::withComponents()
            ->component('ComponentA')->definedBy('Modulith\ArchCheck\Test\Fixtures\ComponentA\\')
            ->component('ComponentB')->definedBy('Modulith\ArchCheck\Test\Fixtures\ComponentB\\')
            ->component('ComponentC')->definedByExpression(
                new Andx(
                    new ResideInOneOfTheseNamespaces('Modulith\ArchCheck\Test\Fixtures\ComponentC\\'),
                    new NotResideInTheseNamespaces('Modulith\ArchCheck\Test\Fixtures\ComponentC\ComponentCA\\')
                )
            )
            ->where('ComponentA')->mustNotDependOnComponents('ComponentB', 'ComponentC')
            ->where('ComponentB')->mustNotDependOnComponents('ComponentA', 'ComponentC')
            ->where('ComponentC')->mustNotDependOnComponents('ComponentA', 'ComponentB')
            ->rules('components should not directly depend on each other.');
        $config = new Config();
        $config->add(ClassSet::fromDir(\FIXTURES_PATH), ...iterator_to_array($rules));

        $runner = new Runner();
        $runner->run($config, new VoidProgress(), TargetPhpVersion::create());
        $violations = $runner->getViolations();

        self::assertEquals(1, $violations->count(), $violations->toString());
        $violationsText = $violations->toString();
        self::assertStringContainsString(
            'Modulith\ArchCheck\Test\Fixtures\ComponentB\ClassBDependingOnAD has 1 violations',
            $violationsText
        );
        self::assertStringContainsString(
            "The dependency 'Modulith\ArchCheck\Test\Fixtures\ComponentA\ClassAWithoutDependencies' violated the expression:",
            $violationsText
        );
        self::assertStringContainsString(
            'NOT resides in one of these namespaces: Modulith\ArchCheck\Test\Fixtures\ComponentA\\',
            $violationsText
        );
    }
}
