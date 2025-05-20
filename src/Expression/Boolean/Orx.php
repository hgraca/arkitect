<?php
declare(strict_types=1);

namespace Arkitect\Expression\Boolean;

use Arkitect\Analyzer\ClassDescription;
use Arkitect\Expression\CompositeExpression;
use Arkitect\Expression\Description;
use Arkitect\Expression\Expression;
use Arkitect\Expression\ExpressionCollection;
use Arkitect\Rules\Violation;
use Arkitect\Rules\ViolationMessage;
use Arkitect\Rules\Violations;

final class Orx implements Expression, CompositeExpression
{
    private ExpressionCollection $expressions;

    public function __construct(Expression ...$expressions)
    {
        $this->expressions = new ExpressionCollection(...$expressions);
    }

    public function describe(ClassDescription $theClass, string $because = ''): Description
    {
        return new Description($this->expressions->describeAgainstClass($theClass), $because);
    }

    public function evaluate(ClassDescription $theClass, Violations $violations, string $because = ''): void
    {
        if (0 === $this->expressions->count()) {
            return;
        }

        foreach ($this->expressions as $expression) {
            $newViolations = new Violations();
            $expression->evaluate($theClass, $newViolations, '');
            if (0 === $newViolations->count()) {
                return;
            }
        }

        $violations->add(
            Violation::create(
                $theClass->getFQCN(),
                ViolationMessage::withDescription($this->describe($theClass, $because), 'All OR expressions failed: '),
                $theClass->getFilePath()
            )
        );
    }
}
