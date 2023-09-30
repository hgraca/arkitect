<?php

declare(strict_types=1);

namespace Arkitect\Tests\Unit\RuleBuilders\Architecture;

use Arkitect\ClassSet;
use Arkitect\CLI\Baseline;
use Arkitect\CLI\Config;
use Arkitect\CLI\Progress\VoidProgress;
use Arkitect\CLI\Runner;
use Arkitect\Expression\Boolean\Andx;
use Arkitect\Expression\ForClasses\NotResideInTheseNamespaces;
use Arkitect\Expression\ForClasses\ResideInOneOfTheseNamespaces;
use Arkitect\RuleBuilders\Architecture\Architecture;
use Arkitect\Rules\Violation;
use Arkitect\Tests\Unit\AbstractUnitTest;

class ArchitectureTest extends AbstractUnitTest
{
    public function test_it_should_see_violations_only_outside_exclusions(): void
    {
        $rules = Architecture::withComponents()
            ->component('ComponentA')->definedBy('Arkitect\Tests\Fixtures\ComponentA\\')
            ->component('ComponentB')->definedBy('Arkitect\Tests\Fixtures\ComponentB\\')
            ->component('ComponentC')->definedByExpression(
                new Andx(
                    new ResideInOneOfTheseNamespaces('Arkitect\Tests\Fixtures\ComponentC\\'),
                    new NotResideInTheseNamespaces('Arkitect\Tests\Fixtures\ComponentC\ComponentCA\\')
                )
            )
            ->where('ComponentA')->mustNotDependOnComponents('ComponentB', 'ComponentC')
            ->where('ComponentB')->mustNotDependOnComponents('ComponentA', 'ComponentC')
            ->where('ComponentC')->mustNotDependOnComponents('ComponentA', 'ComponentB')
            ->rules('components should not directly depend on each other.');
        $config = new Config();
        $config->add(ClassSet::fromDir(\FIXTURES_PATH), ...iterator_to_array($rules));

        $runner = new Runner();
        $result = $runner->run($config, Baseline::empty(), new VoidProgress());
        $violations = $result->getViolations();

        self::assertEquals(1, $violations->count());
        self::assertEquals(
            new Violation(
                'Arkitect\Tests\Fixtures\ComponentB\ClassBDependingOnAD',
                <<<'ERROR'
                The dependency 'Arkitect\Tests\Fixtures\ComponentA\ClassAWithoutDependencies' violated the expression:
                {
                    "AND": [
                        {
                            "NOT": [
                                {
                                    "AND": [
                                        "resides in one of these namespaces: Arkitect\\Tests\\Fixtures\\ComponentC\\",
                                        "not resides in one of these namespaces: Arkitect\\Tests\\Fixtures\\ComponentC\\ComponentCA\\"
                                    ]
                                }
                            ]
                        },
                        {
                            "NOT": [
                                "resides in one of these namespaces: Arkitect\\Tests\\Fixtures\\ComponentA\\"
                            ]
                        }
                    ]
                }
                , but should not have those dependencies because components should not directly depend on each other.
                ERROR,
                null,
                'ComponentB/ClassBDependingOnAD.php'
            ),
            $violations->get(0)
        );
    }
}
