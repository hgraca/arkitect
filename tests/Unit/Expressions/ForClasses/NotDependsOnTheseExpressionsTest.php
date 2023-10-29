<?php

declare(strict_types=1);

namespace Modulith\ArchCheck\Test\Unit\Expressions\ForClasses;

use Modulith\ArchCheck\Expression\Boolean\Andx;
use Modulith\ArchCheck\Expression\ForClasses\NotDependsOnTheseExpressions;
use Modulith\ArchCheck\Expression\ForClasses\NotResideInTheseNamespaces;
use Modulith\ArchCheck\Expression\ForClasses\ResideInOneOfTheseNamespaces;
use Modulith\ArchCheck\Rules\Violations;
use Modulith\ArchCheck\Test\Fixtures\ComponentB\ClassBDependingOnAD;
use Modulith\ArchCheck\Test\Unit\AbstractUnitTest;

class NotDependsOnTheseExpressionsTest extends AbstractUnitTest
{
    public function test_it_should_not_see_violations_in_exclusions(): void
    {
        $notDependOn = new NotDependsOnTheseExpressions(
            new Andx(
                new ResideInOneOfTheseNamespaces('Modulith\ArchCheck\Test\Fixtures\ComponentC\\'),
                new NotResideInTheseNamespaces('Modulith\ArchCheck\Test\Fixtures\ComponentC\ComponentCA\\')
            )
        );

        $classDescription = $this->getClassDescription(ClassBDependingOnAD::class);
        $because = 'we want to add this rule for our software';
        $violations = new Violations();
        $notDependOn->evaluate($classDescription, $violations, $because);

        self::assertEquals(0, $violations->count());
    }

    public function test_it_should_see_violations(): void
    {
        $notDependOn = new NotDependsOnTheseExpressions(
            new ResideInOneOfTheseNamespaces('Modulith\ArchCheck\Test\Fixtures\ComponentA\\')
        );

        $classDescription = $this->getClassDescription(ClassBDependingOnAD::class);
        $because = 'we want to add this rule for our software';
        $violations = new Violations();
        $notDependOn->evaluate($classDescription, $violations, $because);

        self::assertEquals(1, $violations->count());
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
            'resides in one of these namespaces: Modulith\ArchCheck\Test\Fixtures\ComponentA\\',
            $violationsText
        );
    }

    public function test_it_should_see_violations_only_outside_exclusions(): void
    {
        $notDependOn = new NotDependsOnTheseExpressions(
            new ResideInOneOfTheseNamespaces('Modulith\ArchCheck\Test\Fixtures\ComponentA\\'),
            new Andx(
                new ResideInOneOfTheseNamespaces('Modulith\ArchCheck\Test\Fixtures\ComponentC\\'),
                new NotResideInTheseNamespaces('Modulith\ArchCheck\Test\Fixtures\ComponentC\ComponentCA\\')
            )
        );

        $classDescription = $this->getClassDescription(ClassBDependingOnAD::class);
        $because = 'we want to add this rule for our software';
        $violations = new Violations();
        $notDependOn->evaluate($classDescription, $violations, $because);

        self::assertEquals(1, $violations->count());
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
            <<<TXT
  NOT resides in one of these namespaces: Modulith\ArchCheck\Test\Fixtures\ComponentA\
  OR
  NOT (
    resides in one of these namespaces: Modulith\ArchCheck\Test\Fixtures\ComponentC\
    AND
    not resides in one of these namespaces: Modulith\ArchCheck\Test\Fixtures\ComponentC\ComponentCA\
  )
TXT
            ,
            $violationsText
        );
    }
}
