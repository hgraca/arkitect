<?php

declare(strict_types=1);

namespace Modulith\ArchCheck\Expression\ForClasses;

use Modulith\ArchCheck\Analyzer\ClassDescription;
use Modulith\ArchCheck\Expression\Description;
use Modulith\ArchCheck\Expression\Expression;
use Modulith\ArchCheck\Rules\Violation;
use Modulith\ArchCheck\Rules\Violations;

final class HaveCorrespondingUnit implements Expression
{
    /** @var \Closure */
    private $inferFqnFunction;

    public function __construct(\Closure $inferFqnFunction)
    {
        $this->inferFqnFunction = $inferFqnFunction;
    }

    public function describe(ClassDescription $theClass, string $because = ''): Description
    {
        $correspondingFqn = $this->inferCorrespondingFqn($theClass);

        return new Description("should have a matching unit named: '$correspondingFqn'", $because);
    }

    public function evaluate(ClassDescription $theClass, Violations $violations, string $because = ''): void
    {
        $correspondingFqn = $this->inferCorrespondingFqn($theClass);

        if (
            !trait_exists($correspondingFqn)
            && !class_exists($correspondingFqn)
            && !interface_exists($correspondingFqn)
        ) {
            $violations->add(
                new Violation(
                    $theClass->getFQCN(),
                    $this->describe($theClass, $because)->toString()
                )
            );
        }
    }

    /**
     * @return class-string
     */
    public function inferCorrespondingFqn(ClassDescription $theClass): string
    {
        $inferFqn = $this->inferFqnFunction;

        return $inferFqn($theClass->getFQCN());
    }
}
