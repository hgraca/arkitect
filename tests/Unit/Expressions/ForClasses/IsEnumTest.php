<?php
declare(strict_types=1);

namespace Arkitect\Tests\Unit\Expressions\ForClasses;

use Arkitect\Analyzer\ClassDescription;
use Arkitect\Analyzer\FullyQualifiedClassName;
use Arkitect\Expression\ForClasses\IsEnum;
use Arkitect\Rules\Violations;
use PHPUnit\Framework\TestCase;

class IsEnumTest extends TestCase
{
    public function test_it_should_return_violation_error(): void
    {
        $isEnum = new IsEnum();
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
        $violationError = $isEnum->describe($classDescription, $because)->toString();

        $violations = new Violations();
        $isEnum->evaluate($classDescription, $violations, $because);
        self::assertNotEquals(0, $violations->count());

        $this->assertEquals("HappyIsland should be an enum\nbecause we want to add this rule for our software", $violationError);
    }

    public function test_it_should_return_true_if_is_enum(): void
    {
        $isEnum = new IsEnum();
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
        $violations = new Violations();
        $isEnum->evaluate($classDescription, $violations, $because);
        self::assertEquals(0, $violations->count());
    }
}
