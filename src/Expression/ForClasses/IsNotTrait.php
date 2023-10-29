<?php

declare(strict_types=1);

namespace Modulith\ArchCheck\Expression\ForClasses;

use Modulith\ArchCheck\Analyzer\ClassDescription;
use Modulith\ArchCheck\Expression\Description;
use Modulith\ArchCheck\Expression\Expression;
use Modulith\ArchCheck\Rules\Violation;
use Modulith\ArchCheck\Rules\ViolationMessage;
use Modulith\ArchCheck\Rules\Violations;

class IsNotTrait implements Expression
{
    public function describe(ClassDescription $theClass, string $because = ''): Description
    {
        return new Description("{$theClass->getName()} should not be trait", $because);
    }

    public function evaluate(ClassDescription $theClass, Violations $violations, string $because = ''): void
    {
        if (!$theClass->isTrait()) {
            return;
        }

        $violation = Violation::create(
            $theClass->getFQCN(),
            ViolationMessage::selfExplanatory($this->describe($theClass, $because))
        );

        $violations->add($violation);
    }
}
