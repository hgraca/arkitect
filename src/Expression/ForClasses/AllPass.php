<?php

declare(strict_types=1);

namespace Arkitect\Expression\ForClasses;

use Arkitect\Analyzer\ClassDescription;
use Arkitect\Expression\Description;
use Arkitect\Expression\Expression;
use Arkitect\Rules\Violations;

final class AllPass implements Expression
{
    public function describe(ClassDescription $theClass, string $because = ''): Description
    {
        return new Description('all classes pass this expression', $because);
    }

    public function evaluate(ClassDescription $theClass, Violations $violations, string $because = ''): void
    {
    }
}
