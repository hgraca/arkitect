<?php

declare(strict_types=1);

namespace Arkitect\Tests\Unit\Expressions\Boolean;

use Arkitect\Analyzer\ClassDescriptionBuilder;
use Arkitect\Expression\Boolean\Andx;
use Arkitect\Expression\ForClasses\Extend;
use Arkitect\Expression\ForClasses\Implement;
use Arkitect\Rules\Violations;
use PHPUnit\Framework\TestCase;

class AndxTest extends TestCase
{
    public function test_it_should_pass_the_rule(): void
    {
        $interface = 'interface';
        $class = 'SomeClass';
        $classDescription = (new ClassDescriptionBuilder())
            ->setFilePath('src/Foo.php')
            ->setClassName('HappyIsland')
            ->addInterface($interface, 11)
            ->addExtends($class, 10)
            ->build();
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
        $classDescription = (new ClassDescriptionBuilder())
            ->setFilePath('src/Foo.php')
            ->setClassName('HappyIsland')
            ->addInterface($interface, 11)
            ->addExtends($class, 10)
            ->build();
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

        $classDescription = (new ClassDescriptionBuilder())
            ->setFilePath('src/Foo.php')
            ->setClassName('HappyIsland')
            ->addInterface($interface, 11)
            ->build();

        $implementConstraint = new Implement($interface);
        $extendsConstraint = new Extend($class);
        $andConstraint = new Andx($implementConstraint, $extendsConstraint);

        $because = 'reasons';
        $violationError = $andConstraint->describe($classDescription, $because)->toString();

        $violations = new Violations();
        $andConstraint->evaluate($classDescription, $violations, $because);
        self::assertNotEquals(0, $violations->count());

        self::assertEquals(
            "(\nshould implement SomeInterface\nbecause reasons\nAND\nshould extend one of these classes: SomeClass\nbecause reasons\n)\nbecause reasons",
            $violationError
        );
        self::assertEquals(
            <<<'STR'
            The class 'HappyIsland' violated the expression
            should extend one of these classes: SomeClass
            from the rule
            (
            should implement SomeInterface
            because reasons
            AND
            should extend one of these classes: SomeClass
            because reasons
            )
            because reasons
            STR,
            $violations->get(0)->getError(),
        );
    }
}
