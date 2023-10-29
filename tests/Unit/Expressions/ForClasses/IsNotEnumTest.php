<?php
declare(strict_types=1);

namespace Modulith\ArchCheck\Test\Unit\Expressions\ForClasses;

use Modulith\ArchCheck\Analyzer\ClassDescription;
use Modulith\ArchCheck\Analyzer\FullyQualifiedClassName;
use Modulith\ArchCheck\Expression\ForClasses\IsNotEnum;
use Modulith\ArchCheck\Rules\Violations;
use PHPUnit\Framework\TestCase;

class IsNotEnumTest extends TestCase
{
    public function test_it_should_return_violation_error(): void
    {
        $isEnum = new IsNotEnum();
        $classDescription = new ClassDescription(
            FullyQualifiedClassName::fromString('HappyIsland'),
            [],
            [],
            null,
            false,
            false,
            false,
            false,
            true
        );
        $because = 'we want to add this rule for our software';
        $violationError = $isEnum->describe($classDescription, $because)->toString();

        $violations = new Violations();
        $isEnum->evaluate($classDescription, $violations, $because);
        self::assertNotEquals(0, $violations->count());

        $this->assertEquals("HappyIsland should not be an enum\nbecause we want to add this rule for our software", $violationError);
    }

    public function test_it_should_return_true_if_is_not_enum(): void
    {
        $isEnum = new IsNotEnum();
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
        $isEnum->evaluate($classDescription, $violations, $because);
        self::assertEquals(0, $violations->count());
    }
}
