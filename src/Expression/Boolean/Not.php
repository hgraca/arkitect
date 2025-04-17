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

final class Not implements Expression, CompositeExpression
{
    /** @var Expression */
    private $expression;

    public function __construct(Expression $expression)
    {
        $this->expression = $expression;
    }

    public function describe(ClassDescription $theClass, string $because = ''): Description
    {
        return new Description(
            (new ExpressionCollection($this->expression))->describeAgainstClass($theClass, 'NOT'),
            $because
        );
    }

    public function evaluate(ClassDescription $theClass, Violations $violations, string $because = ''): void
    {
        $newViolations = new Violations();
        $this->expression->evaluate($theClass, $newViolations, $because);
        if (0 !== $newViolations->count()) {
            return;
        }

        $violations->add(Violation::create(
            $theClass->getFQCN(),
            ViolationMessage::selfExplanatory($this->describe($theClass, $because)),
            $theClass->getFilePath()
        ));
    }
}
