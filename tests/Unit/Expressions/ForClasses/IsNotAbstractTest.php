<?php

declare(strict_types=1);

namespace Arkitect\Tests\Unit\Expressions\ForClasses;

use Arkitect\Analyzer\ClassDescriptionBuilder;
use Arkitect\Expression\ForClasses\IsNotAbstract;
use Arkitect\Rules\Violations;
use PHPUnit\Framework\TestCase;

class IsNotAbstractTest extends TestCase
{
    public function test_it_should_return_violation_error(): void
    {
        $isAbstract = new IsNotAbstract();

        $classDescription = (new ClassDescriptionBuilder())
            ->setFilePath('src/Foo.php')
            ->setClassName('HappyIsland')
            ->setAbstract(true)
            ->build();

        $because = 'we want to add this rule for our software';
        $violationError = $isAbstract->describe($classDescription, $because)->toString();

        $violations = new Violations();
        $isAbstract->evaluate($classDescription, $violations, $because);

        self::assertNotEquals(0, $violations->count());
        self::assertEquals('HappyIsland should not be abstract because we want to add this rule for our software', $violationError);
    }

    public function test_it_should_return_true_if_is_abstract(): void
    {
        $isAbstract = new IsNotAbstract();

        $classDescription = (new ClassDescriptionBuilder())
            ->setFilePath('src/Foo.php')
            ->setClassName('HappyIsland')
            ->build();

        $because = 'we want to add this rule for our software';
        $violations = new Violations();
        $isAbstract->evaluate($classDescription, $violations, $because);

        self::assertEquals(0, $violations->count());
    }
}
