<?php

declare(strict_types=1);

namespace Arkitect\Expression\ForClasses;

use Arkitect\Analyzer\ClassDescription;
use Arkitect\Expression\Description;
use Arkitect\Expression\Expression;
use Arkitect\Rules\Violation;
use Arkitect\Rules\ViolationMessage;
use Arkitect\Rules\Violations;

final class NonePass implements Expression
{
    public function describe(ClassDescription $theClass, string $because = ''): Description
    {
        return new Description('no class passes this expression', $because);
    }

    public function evaluate(ClassDescription $theClass, Violations $violations, string $because = ''): void
    {
        $violations->add(
            Violation::create(
                $theClass->getFQCN(),
                ViolationMessage::withDescription($this->describe($theClass, $because), 'No classes pass this expression: ')
            )
        );
    }
}
