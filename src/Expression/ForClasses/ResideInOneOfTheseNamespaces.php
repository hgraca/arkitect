<?php

declare(strict_types=1);

namespace Modulith\ArchCheck\Expression\ForClasses;

use Modulith\ArchCheck\Analyzer\ClassDescription;
use Modulith\ArchCheck\Expression\Description;
use Modulith\ArchCheck\Expression\Expression;
use Modulith\ArchCheck\Expression\MergeableExpression;
use Modulith\ArchCheck\Rules\Violation;
use Modulith\ArchCheck\Rules\ViolationMessage;
use Modulith\ArchCheck\Rules\Violations;

class ResideInOneOfTheseNamespaces implements Expression, MergeableExpression
{
    /** @var string[] */
    private $namespaces;

    public function __construct(string ...$namespaces)
    {
        $this->namespaces = array_unique($namespaces);
    }

    public function describe(ClassDescription $theClass, string $because = ''): Description
    {
        $descr = implode(', ', $this->namespaces);

        return new Description("resides in one of these namespaces: $descr", $because);
    }

    public function evaluate(ClassDescription $theClass, Violations $violations, string $because = ''): void
    {
        $resideInNamespace = false;
        foreach ($this->namespaces as $namespace) {
            if ($theClass->namespaceMatches($namespace.'*')) {
                $resideInNamespace = true;
            }
        }

        if (!$resideInNamespace) {
            $violation = Violation::create(
                $theClass->getFQCN(),
                ViolationMessage::selfExplanatory($this->describe($theClass, $because))
            );
            $violations->add($violation);
        }
    }

    public function mergeWith(Expression $expression): Expression
    {
        if (!$expression instanceof self) {
            throw new \InvalidArgumentException('Can not merge expressions. The given expression should be of type '.\get_class($this).' but is of type '.\get_class($expression));
        }

        return new self(...array_merge($this->namespaces, $expression->namespaces));
    }
}
