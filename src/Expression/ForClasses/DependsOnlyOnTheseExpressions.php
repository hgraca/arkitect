<?php

declare(strict_types=1);

namespace Modulith\ArchCheck\Expression\ForClasses;

use Modulith\ArchCheck\Analyzer\ClassDependencyCollection;
use Modulith\ArchCheck\Analyzer\ClassDescription;
use Modulith\ArchCheck\Exceptions\ClassFileNotFoundException;
use Modulith\ArchCheck\Exceptions\FailOnFirstViolationException;
use Modulith\ArchCheck\Expression\Description;
use Modulith\ArchCheck\Expression\Expression;
use Modulith\ArchCheck\Expression\ExpressionCollection;
use Modulith\ArchCheck\Rules\Violation;
use Modulith\ArchCheck\Rules\ViolationMessage;
use Modulith\ArchCheck\Rules\Violations;

class DependsOnlyOnTheseExpressions implements Expression
{
    /** @var ExpressionCollection */
    private $expressions;

    public function __construct(Expression ...$expressions)
    {
        $this->expressions = new ExpressionCollection(...$expressions);
    }

    public function describe(ClassDescription $theClass, string $because = ''): Description
    {
        $expressionsDescriptions = '';
        foreach ($this->expressions as $expression) {
            $expressionsDescriptions .= $expression->describe($theClass)->toString()."\n";
        }

        return new Description(
            "should depend only on classes in one of the given expressions: \n"
            .$expressionsDescriptions,
            $because
        );
    }

    /**
     * @throws \ReflectionException
     * @throws FailOnFirstViolationException
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

            if (!$this->expressions->hasComplianceWith($dependencyClassDescription)) {
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
