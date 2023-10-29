<?php

declare(strict_types=1);

namespace Modulith\ArchCheck\Test\Unit\Expressions\ForClasses;

use Modulith\ArchCheck\Analyzer\ClassDescription;
use Modulith\ArchCheck\Analyzer\FullyQualifiedClassName;
use Modulith\ArchCheck\Expression\ForClasses\IsNotInterface;
use Modulith\ArchCheck\Rules\Violations;
use PHPUnit\Framework\TestCase;

class IsNotInterfaceTest extends TestCase
{
    public function test_it_should_return_violation_error(): void
    {
        $isFinal = new IsNotInterface();
        $classDescription = new ClassDescription(
            FullyQualifiedClassName::fromString('HappyIsland'),
            [],
            [],
            null,
            false,
            false,
            true,
            false,
            false
        );
        $because = 'we want to add this rule for our software';
        $violationError = $isFinal->describe($classDescription, $because)->toString();

        $violations = new Violations();
        $isFinal->evaluate($classDescription, $violations, $because);
        self::assertNotEquals(0, $violations->count());

        $this->assertEquals("HappyIsland should not be an interface\nbecause we want to add this rule for our software", $violationError);
    }

    public function test_it_should_return_true_if_is_not_interface(): void
    {
        $isFinal = new IsNotInterface();
        $classDescription = new ClassDescription(
            FullyQualifiedClassName::fromString('HappyIsland'),
            [],
            [],
            null,
            false,
            false,
            false,
            false,
            false
        );
        $because = 'we want to add this rule for our software';
        $violations = new Violations();
        $isFinal->evaluate($classDescription, $violations, $because);
        self::assertEquals(0, $violations->count());
    }
}
