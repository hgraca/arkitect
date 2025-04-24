<?php

declare(strict_types=1);

namespace Arkitect\Expression\Boolean;

use Arkitect\Analyzer\ClassDescription;
use Arkitect\Analyzer\ClassDescriptionRegistry;
use Arkitect\Expression\ClassRegistryAwareExpression;
use Arkitect\Expression\Description;
use Arkitect\Expression\Expression;
use Arkitect\Rules\Violation;
use Arkitect\Rules\ViolationMessage;
use Arkitect\Rules\Violations;

final class Not implements Expression, ClassRegistryAwareExpression
{
    /** @var Expression */
    private $expression;

    /**
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private ClassDescriptionRegistry $classDescriptionRegistry;

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
        $this->classDescriptionRegistry->injectInto($this->expression);
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

    public function injectClassDescriptionRegistry(ClassDescriptionRegistry $classRegistry): void
    {
        $this->classDescriptionRegistry = $classRegistry;
    }
}
