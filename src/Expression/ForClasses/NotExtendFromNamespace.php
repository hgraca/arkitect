<?php

declare(strict_types=1);

namespace Arkitect\Expression\ForClasses;

use Arkitect\Analyzer\ClassDescription;
use Arkitect\Analyzer\FullyQualifiedClassName;
use Arkitect\Expression\Description;
use Arkitect\Expression\Expression;
use Arkitect\Rules\Violation;
use Arkitect\Rules\ViolationMessage;
use Arkitect\Rules\Violations;

class NotExtendFromNamespace implements Expression
{
    private string $namespace;

    /** @var array<string> */
    private array $exceptions;

    public function __construct(string $namespace, array $exceptions = [])
    {
        $this->namespace = $namespace;
        $this->exceptions = $exceptions;
    }

    public function describe(ClassDescription $theClass, string $because = ''): Description
    {
        $exceptDescription = [] !== $this->exceptions ? ', except for ['.implode(', ', $this->exceptions).']' : '';

        return new Description(
            "should not extend from namespace {$this->namespace}{$exceptDescription}",
            $because
        );
    }

    public function evaluate(ClassDescription $theClass, Violations $violations, string $because = ''): void
    {
        foreach ($theClass->getExtends() as $parent) {
            if ($this->parentIsForbiddenNamespace($parent) && !$this->parentIsExceptionNamespace($parent)) {
                $violation = Violation::create(
                    $theClass->getFQCN(),
                    ViolationMessage::selfExplanatory($this->describe($theClass, $because)),
                    $theClass->getFilePath(),
                );

                $violations->add($violation);
            }
        }
    }

    public function parentIsExceptionNamespace(FullyQualifiedClassName $parent): bool
    {
        foreach ($this->exceptions as $exceptionNamespace) {
            if ($this->parentMatchesExceptionNamespace($parent, $exceptionNamespace)) {
                return true;
            }
        }

        return false;
    }

    public function parentIsForbiddenNamespace(FullyQualifiedClassName $parent): bool
    {
        return str_starts_with($parent->toString(), $this->namespace);
    }

    public function parentMatchesExceptionNamespace(FullyQualifiedClassName $parent, string $exceptionNamespace): bool
    {
        return $parent->toString() === $exceptionNamespace
            || str_starts_with($parent->toString(), $exceptionNamespace);
    }
}
