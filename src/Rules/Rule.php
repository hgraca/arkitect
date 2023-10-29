<?php
declare(strict_types=1);

namespace Modulith\ArchCheck\Rules;

class Rule
{
    public static function allClasses(): AllClasses
    {
        return new AllClasses();
    }
}
