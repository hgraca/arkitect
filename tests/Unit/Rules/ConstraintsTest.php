<?php

declare(strict_types=1);

namespace Modulith\ArchCheck\Test\Unit\Rules;

use Modulith\ArchCheck\Analyzer\ClassDescription;
use Modulith\ArchCheck\Analyzer\ClassDescriptionBuilder;
use Modulith\ArchCheck\Expression\Description;
use Modulith\ArchCheck\Expression\Expression;
use Modulith\ArchCheck\Rules\Constraints;
use Modulith\ArchCheck\Rules\Violation;
use Modulith\ArchCheck\Rules\ViolationMessage;
use Modulith\ArchCheck\Rules\Violations;
use PHPUnit\Framework\TestCase;

class ConstraintsTest extends TestCase
{
    public function test_it_should_not_add_to_violation_if_constraint_is_not_violated(): void
    {
        $trueExpression = new class() implements Expression {
            public function describe(ClassDescription $theClass, string $because = ''): Description
            {
                return new Description('');
            }

            public function evaluate(ClassDescription $theClass, Violations $violations, string $because = ''): void
            {
            }
        };

        $expressionStore = new Constraints();
        $expressionStore->add($trueExpression);
        $violations = new Violations();
        $because = 'we want to add this rule for our software';

        $cb = new ClassDescriptionBuilder();
        $cb->setClassName('Banana');

        $expressionStore->checkAll(
            $cb->build(),
            $violations,
            $because
        );

        $this->assertCount(0, $violations);
    }

    public function test_it_should_add_to_violation_store_if_constraint_is_violated(): void
    {
        $falseExpression = new class() implements Expression {
            public function describe(ClassDescription $theClass, string $because = ''): Description
            {
                return new Description('bar', 'we want to add this rule');
            }

            public function evaluate(ClassDescription $theClass, Violations $violations, string $because = ''): void
            {
                $violation = Violation::create(
                    $theClass->getFQCN(),
                    ViolationMessage::selfExplanatory($this->describe($theClass, $because))
                );

                $violations->add($violation);
            }
        };

        $expressionStore = new Constraints();
        $expressionStore->add($falseExpression);
        $violations = new Violations();
        $because = 'we want to add this rule for our software';

        $cb = new ClassDescriptionBuilder();
        $cb->setClassName('Banana');

        $expressionStore->checkAll(
            $cb->build(),
            $violations,
            $because
        );

        $this->assertCount(1, $violations);
    }
}
