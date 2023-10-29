<?php

declare(strict_types=1);

namespace Modulith\ArchCheck\Expression\Boolean;

use Modulith\ArchCheck\Analyzer\ClassDescription;
use Modulith\ArchCheck\Expression\Description;
use Modulith\ArchCheck\Expression\Expression;
use Modulith\ArchCheck\Rules\Violation;
use Modulith\ArchCheck\Rules\ViolationMessage;
use Modulith\ArchCheck\Rules\Violations;

final class Not implements Expression
{
    /** @var Expression */
    private $expression;

    public function __construct(Expression $expression)
    {
        $this->expression = $expression;
    }

    public function describe(ClassDescription $theClass, string $because = ''): Description
    {
        return new Description('NOT '.$this->expression->describe($theClass)->toString(), $because);
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
            ViolationMessage::selfExplanatory($this->describe($theClass, $because))
        ));
    }
}
