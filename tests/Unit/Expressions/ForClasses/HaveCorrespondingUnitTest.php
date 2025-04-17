<?php

declare(strict_types=1);

namespace Arkitect\Tests\Unit\Expressions\ForClasses;

use Arkitect\Analyzer\ClassDescriptionBuilder;
use Arkitect\Expression\ForClasses\HaveCorrespondingUnit;
use Arkitect\Rules\Violations;
use Arkitect\Tests\Unit\Expressions\ForClasses\DummyClasses\Cat;
use Arkitect\Tests\Unit\Expressions\ForClasses\DummyClasses\Dog;
use PHPUnit\Framework\TestCase;

class HaveCorrespondingUnitTest extends TestCase
{
    public function test_it_should_pass_the_validation(): void
    {
        $class = Cat::class;
        $classDescription = (new ClassDescriptionBuilder())
            ->setFilePath('src/Foo.php')
            ->setClassName($class)
            ->build();
        $constraint = new HaveCorrespondingUnit(
            function ($fqn) {
                return $fqn.'TestCase';
            }
        );

        $because = 'we want all our command handlers to have a test';
        $violations = new Violations();
        $constraint->evaluate($classDescription, $violations, $because);

        self::assertEquals(0, $violations->count());
    }

    public function test_it_should_return_violation_error(): void
    {
        $class = Dog::class;
        $classDescription = (new ClassDescriptionBuilder())
            ->setFilePath('src/Foo.php')
            ->setClassName($class)
            ->build();
        $constraint = new HaveCorrespondingUnit(
            function ($fqn) {
                return $fqn.'TestCase';
            }
        );

        $because = 'we want all our command handlers to have a test';
        $violations = new Violations();
        $constraint->evaluate($classDescription, $violations, $because);

        self::assertNotEquals(0, $violations->count());

        $violationError = $constraint->describe($classDescription, $because)->toString();
        self::assertEquals(
            'should have a matching unit named: '
            ."'Arkitect\Tests\Unit\Expressions\ForClasses\DummyClasses\DogTestCase' because $because",
            $violationError
        );
    }
}
