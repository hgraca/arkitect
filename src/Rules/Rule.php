<?php
declare(strict_types=1);

namespace Arkitect\Rules;

use Arkitect\Analyzer\ClassDescriptionRegistry;

class Rule
{
    public static function allClasses(): AllClasses
    {
        return new AllClasses(ClassDescriptionRegistry::get());
    }
}
