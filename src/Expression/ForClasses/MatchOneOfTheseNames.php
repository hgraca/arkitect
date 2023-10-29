<?php

declare(strict_types=1);

namespace Modulith\ArchCheck\Expression\ForClasses;

use Modulith\ArchCheck\Analyzer\ClassDescription;
use Modulith\ArchCheck\Analyzer\FullyQualifiedClassName;
use Modulith\ArchCheck\Expression\Description;
use Modulith\ArchCheck\Expression\Expression;
use Modulith\ArchCheck\Rules\Violation;
use Modulith\ArchCheck\Rules\ViolationMessage;
use Modulith\ArchCheck\Rules\Violations;

class MatchOneOfTheseNames implements Expression
{
    /** @var array<string> */
    private $names;

    public function __construct(array $names)
    {
        $this->names = $names;
    }

    public function describe(ClassDescription $theClass, string $because = ''): Description
    {
        $names = implode(', ', $this->names);

        return new Description("should have a name that matches {$names}", $because);
    }

    public function evaluate(ClassDescription $theClass, Violations $violations, string $because = ''): void
    {
        $fqcn = FullyQualifiedClassName::fromString($theClass->getFQCN());
        $matches = false;
        foreach ($this->names as $name) {
            $matches = $matches || $fqcn->classMatches($name);
        }

        if (!$matches) {
            $violation = Violation::create(
                $theClass->getFQCN(),
                ViolationMessage::selfExplanatory($this->describe($theClass, $because))
            );
            $violations->add($violation);
        }
    }
}
