<?php

declare(strict_types=1);

namespace Arkitect\Expression\ForClasses;

use Arkitect\Analyzer\ClassDescription;
use Arkitect\Expression\Description;
use Arkitect\Expression\Expression;
use Arkitect\Rules\Violation;
use Arkitect\Rules\ViolationMessage;
use Arkitect\Rules\Violations;

class IsNotEnum implements Expression
{
    public function describe(ClassDescription $theClass, string $because = ''): Description
    {
        return new Description("{$theClass->getName()} should not be an enum", $because);
    }

    public function evaluate(ClassDescription $theClass, Violations $violations, string $because = ''): void
    {
        if (false === $theClass->isEnum()) {
            return;
        }

        $violation = Violation::create(
            $theClass->getFQCN(),
            ViolationMessage::selfExplanatory($this->describe($theClass, $because)),
            $theClass->getFilePath()
        );

        $violations->add($violation);
    }
}
