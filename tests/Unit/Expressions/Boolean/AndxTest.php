<?php

declare(strict_types=1);

namespace Modulith\ArchCheck\Test\Unit\Expressions\Boolean;

use Modulith\ArchCheck\Analyzer\ClassDescription;
use Modulith\ArchCheck\Analyzer\ClassDescriptionBuilder;
use Modulith\ArchCheck\Analyzer\FullyQualifiedClassName;
use Modulith\ArchCheck\Expression\Boolean\Andx;
use Modulith\ArchCheck\Expression\ForClasses\Extend;
use Modulith\ArchCheck\Expression\ForClasses\Implement;
use Modulith\ArchCheck\Rules\Violations;
use PHPUnit\Framework\TestCase;

class AndxTest extends TestCase
{
    public function test_it_should_return_no_violation_when_empty(): void
    {
        $and = new Andx();

        $classDescription = (new ClassDescriptionBuilder())
            ->setClassName('My\Class')
            ->setExtends('My\BaseClass', 10)
            ->build();

        $violations = new Violations();
        $and->evaluate($classDescription, $violations, 'because');

        self::assertEquals(0, $violations->count());
    }

    public function test_it_should_pass_the_rule(): void
    {
        $interface = 'interface';
        $class = 'SomeClass';
        $classDescription = new ClassDescription(
            FullyQualifiedClassName::fromString('HappyIsland'),
            [],
            [FullyQualifiedClassName::fromString($interface)],
            FullyQualifiedClassName::fromString($class),
            false,
            false,
            false,
            false,
            false
        );
        $implementConstraint = new Implement($interface);
        $extendsConstraint = new Extend($class);
        $andConstraint = new Andx($implementConstraint, $extendsConstraint);

        $because = 'reasons';
        $violations = new Violations();
        $andConstraint->evaluate($classDescription, $violations, $because);

        self::assertEquals(0, $violations->count());
    }

    public function test_it_should_pass_the_rule_when_and_is_empty(): void
    {
        $interface = 'interface';
        $class = 'SomeClass';
        $classDescription = new ClassDescription(
            FullyQualifiedClassName::fromString('HappyIsland'),
            [],
            [FullyQualifiedClassName::fromString($interface)],
            FullyQualifiedClassName::fromString($class),
            false,
            false,
            false,
            false,
            false
        );
        $andConstraint = new Andx();

        $because = 'reasons';
        $violations = new Violations();
        $andConstraint->evaluate($classDescription, $violations, $because);

        self::assertEquals(0, $violations->count());
    }

    public function test_it_should_not_pass_the_rule(): void
    {
        $interface = 'SomeInterface';
        $class = 'SomeClass';

        $classDescription = new ClassDescription(
            FullyQualifiedClassName::fromString('HappyIsland'),
            [],
            [FullyQualifiedClassName::fromString($interface)],
            null,
            false,
            false,
            false,
            false,
            false
        );

        $implementConstraint = new Implement($interface);
        $extendsConstraint = new Extend($class);
        $andConstraint = new Andx($implementConstraint, $extendsConstraint);

        $because = 'reasons';
        $violationError = $andConstraint->describe($classDescription, $because)->toString();

        $violations = new Violations();
        $andConstraint->evaluate($classDescription, $violations, $because);
        self::assertNotEquals(0, $violations->count());

        $this->assertEquals(
            "(\n"
            ."  should implement SomeInterface\n"
            ."  because reasons\n"
            ."  AND\n"
            ."  should extend SomeClass\n"
            ."  because reasons\n"
            .")\n"
            .'because reasons',
            $violationError
        );
        $this->assertEquals(
            "The class 'HappyIsland' violated the expression\n"
            ."should extend SomeClass\n"
            ."from the rule\n"
            ."(\n"
            ."  should implement SomeInterface\n"
            ."  because reasons\n"
            ."  AND\n"
            ."  should extend SomeClass\n"
            ."  because reasons\n"
            .")\n"
            .'because reasons',
            $violations->get(0)->getError()
        );
    }
}
