<?php

declare(strict_types=1);

namespace Modulith\ArchCheck\Expression\ForClasses;

use Modulith\ArchCheck\Analyzer\ClassDescription;
use Modulith\ArchCheck\Expression\Description;
use Modulith\ArchCheck\Expression\Expression;
use Modulith\ArchCheck\Rules\Violation;
use Modulith\ArchCheck\Rules\ViolationMessage;
use Modulith\ArchCheck\Rules\Violations;

class ContainDocBlockLike implements Expression
{
    /** @var string */
    private $docBlock;

    public function __construct(string $docBlock)
    {
        $this->docBlock = $docBlock;
    }

    public function describe(ClassDescription $theClass, string $because = ''): Description
    {
        return new Description("should have a doc block that contains {$this->docBlock}", $because);
    }

    public function evaluate(ClassDescription $theClass, Violations $violations, string $because = ''): void
    {
        if (!$theClass->containsDocBlock($this->docBlock)) {
            $violation = Violation::create(
                $theClass->getFQCN(),
                ViolationMessage::selfExplanatory($this->describe($theClass, $because))
            );
            $violations->add($violation);
        }
    }
}
