<?php

declare(strict_types=1);

namespace Arkitect\Tests\Unit\Expressions\ForClasses;

use Arkitect\Analyzer\ClassDescriptionBuilder;
use Arkitect\Expression\ForClasses\NotImplementFromNamespace;
use Arkitect\Rules\Violations;
use PHPUnit\Framework\TestCase;

class NotImplementFromNamespaceTest extends TestCase
{
    public function test_it_should_return_violation_error(): void
    {
        $notExtend = new NotImplementFromNamespace('My');

        $classDescription = (new ClassDescriptionBuilder())
            ->setFilePath('src/Foo.php')
            ->setClassName('HappyIsland')
            ->addInterface('My\BaseClass', 11)
            ->build();
        $because = 'we want to add this rule for our software';

        $violations = new Violations();
        $notExtend->evaluate($classDescription, $violations, $because);

        self::assertEquals(1, $violations->count());
        self::assertEquals(
            "should not implement from namespace My\nbecause we want to add this rule for our software",
            $notExtend->describe($classDescription, $because)->toString() // violation description
        );
    }

    public function test_it_should_not_return_violation_error_if_implements_another_interface(): void
    {
        $notExtend = new NotImplementFromNamespace('My');

        $classDescription = (new ClassDescriptionBuilder())
            ->setFilePath('src/Foo.php')
            ->setClassName('HappyIsland')
            ->addInterface('Other\AnotherClass', 11)
            ->build();

        $violations = new Violations();
        $notExtend->evaluate($classDescription, $violations);

        self::assertEquals(0, $violations->count());
    }

    public function test_it_should_not_return_violation_error_if_implements_from_exclusion_list(): void
    {
        $notExtend = new NotImplementFromNamespace('My', ['My\Yet']);

        $classDescription = (new ClassDescriptionBuilder())
            ->setFilePath('src/Foo.php')
            ->setClassName('HappyIsland')
            ->addInterface('My\Yet\AnotherClass', 11)
            ->build();

        $violations = new Violations();
        $notExtend->evaluate($classDescription, $violations);

        self::assertEquals(0, $violations->count());
    }

    public function test_it_shows_exclusions(): void
    {
        $notExtend = new NotImplementFromNamespace('My', ['My\Yet']);

        $classDescription = (new ClassDescriptionBuilder())
            ->setFilePath('src/Foo.php')
            ->setClassName('HappyIsland')
            ->addInterface('My\BaseClass', 11)
            ->build();
        $because = 'we want to add this rule for our software';

        $violations = new Violations();
        $notExtend->evaluate($classDescription, $violations, $because);

        self::assertEquals(1, $violations->count());
        self::assertEquals(
            "should not implement from namespace My, except for [My\Yet]\nbecause we want to add this rule for our software",
            $notExtend->describe($classDescription, $because)->toString() // violation description
        );
    }
}
