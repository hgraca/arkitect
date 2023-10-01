<?php

declare(strict_types=1);

namespace Arkitect\Expression\ForClasses;

use Arkitect\Analyzer\ClassDependencyCollection;
use Arkitect\Analyzer\ClassDescription;
use Arkitect\Exceptions\ClassFileNotFoundException;
use Arkitect\Exceptions\FailOnFirstViolationException;
use Arkitect\Expression\Boolean\Not;
use Arkitect\Expression\Description;
use Arkitect\Expression\Expression;
use Arkitect\Expression\ExpressionCollection;
use Arkitect\Rules\Violation;
use Arkitect\Rules\ViolationMessage;
use Arkitect\Rules\Violations;

class NotDependsOnTheseExpressions implements Expression
{
    /** @var ExpressionCollection */
    private $expressions;

    public function __construct(Expression ...$expressions)
    {
        $this->expressions = new ExpressionCollection();
        foreach ($expressions as $expression) {
            $this->expressions->addExpression(new Not($expression));
        }
    }

    public function describe(ClassDescription $theClass, string $because = ''): Description
    {
        $expressionsDescriptions = '';
        foreach ($this->expressions as $expression) {
            $expressionsDescriptions .= $expression->describe($theClass)->toString()."\n";
        }

        return new Description(
            "should not depend on classes in any of these expressions: \n"
            .trim($expressionsDescriptions),
            $because
        );
    }

    /**
     * @throws FailOnFirstViolationException
     * @throws \ReflectionException
     * @throws ClassFileNotFoundException
     */
    public function evaluate(ClassDescription $theClass, Violations $violations, string $because = ''): void
    {
        $dependencies = (new ClassDependencyCollection(...$theClass->getDependencies()))->removeDuplicateDependencies();

        foreach ($dependencies as $dependency) {
            if (
                '' === $dependency->getFQCN()->namespace()
                || $theClass->namespaceMatches($dependency->getFQCN()->namespace())
            ) {
                continue;
            }

            $dependencyClassDescription = $dependency->getClassDescription();

            if ($this->expressions->hasViolationBy($dependencyClassDescription)) {
                $violations->add(
                    Violation::create(
                        $theClass->getFQCN(),
                        ViolationMessage::withDescription(
                            $this->describe($theClass, $because),
                            "The dependency '".$dependencyClassDescription->getFQCN()."' violated the expression: \n"
                            .$this->expressions->describeAgainstClass($dependencyClassDescription)."\n"
                        )
                    )
                );
            }
        }
    }
}
