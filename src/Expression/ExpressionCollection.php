<?php

declare(strict_types=1);

namespace Arkitect\Expression;

use Arkitect\Analyzer\ClassDescription;
use Arkitect\Rules\Violations;

/**
 * @implements \IteratorAggregate<Expression>
 */
final class ExpressionCollection implements \IteratorAggregate
{
    /**
     * @var array<Expression>
     */
    private $expressionList = [];

    public function __construct(Expression ...$expressionList)
    {
        foreach ($expressionList as $newExpression) {
            $this->addExpression($newExpression);
        }
    }

    /**
     * @return \Iterator<array-key, Expression>
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->expressionList);
    }

    /**
     * Returns true if there is at least one expression that is not violated by the class.
     */
    public function hasComplianceWith(ClassDescription $dependencyClassDescription): bool
    {
        foreach ($this->expressionList as $expression) {
            $newViolations = new Violations();
            $expression->evaluate($dependencyClassDescription, $newViolations);
            if (0 === $newViolations->count()) {
                return true;
            }
        }

        return false;
    }

    public function describeAgainstClass(ClassDescription $theClass, string $operation = 'OR'): string
    {
        $expressionsDescriptions = [];
        foreach ($this->expressionList as $expression) {
            $expressionsDescriptions[] = $expression instanceof CompositeExpression
                ? $this->describeNestedCompositeExpression($theClass, $expression)
                : $expression->describe($theClass)->toString();
        }

        return "\n".json_encode([strtoupper($operation) => $expressionsDescriptions], JSON_PRETTY_PRINT)."\n";
    }

    public function addExpression(Expression $newExpression): void
    {
        foreach ($this->expressionList as $index => $existingExpression) {
            if (
                $newExpression instanceof $existingExpression
                && $newExpression instanceof MergeableExpression
                && $existingExpression instanceof MergeableExpression
            ) {
                $this->expressionList[$index] = $existingExpression->mergeWith($newExpression);

                return;
            }
        }
        $this->expressionList[] = $newExpression;
    }

    public function count(): int
    {
        return \count($this->expressionList);
    }

    public function first(): ?Expression
    {
        return $this->expressionList[0] ?? null;
    }

    private function describeNestedCompositeExpression(ClassDescription $theClass, Expression $expression): array
    {
        return json_decode($expression->describe($theClass)->toString(), true);
    }
}
