<?php

declare(strict_types=1);

namespace Modulith\ArchCheck\Expression\ForClasses;

use Modulith\ArchCheck\Analyzer\ClassDependency;
use Modulith\ArchCheck\Analyzer\ClassDescription;
use Modulith\ArchCheck\Expression\Description;
use Modulith\ArchCheck\Expression\Expression;
use Modulith\ArchCheck\Rules\Violation;
use Modulith\ArchCheck\Rules\ViolationMessage;
use Modulith\ArchCheck\Rules\Violations;

class NotHaveDependencyOutsideNamespace implements Expression
{
    /** @var string */
    private $namespace;
    /** @var array */
    private $externalDependenciesToExclude;

    public function __construct(string $namespace, array $externalDependenciesToExclude = [])
    {
        $this->namespace = $namespace;
        $this->externalDependenciesToExclude = $externalDependenciesToExclude;
    }

    public function describe(ClassDescription $theClass, string $because = ''): Description
    {
        return new Description("should not depend on classes outside namespace {$this->namespace}", $because);
    }

    public function evaluate(ClassDescription $theClass, Violations $violations, string $because = ''): void
    {
        $namespace = $this->namespace;
        $depends = function (ClassDependency $dependency) use ($namespace): bool {
            return !$dependency->getFQCN()->matches($namespace);
        };

        $dependencies = $theClass->getDependencies();
        $externalDeps = array_filter($dependencies, $depends);

        /** @var ClassDependency $externalDep */
        foreach ($externalDeps as $externalDep) {
            if ($externalDep->matchesOneOf(...$this->externalDependenciesToExclude)) {
                continue;
            }

            $violation = Violation::createWithErrorLine(
                $theClass->getFQCN(),
                ViolationMessage::withDescription(
                    $this->describe($theClass, $because),
                    "depends on {$externalDep->getFQCN()->toString()}"
                ),
                $externalDep->getLine()
            );
            $violations->add($violation);
        }
    }
}
