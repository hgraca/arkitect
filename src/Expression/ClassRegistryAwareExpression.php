<?php

declare(strict_types=1);

namespace Arkitect\Expression;

use Arkitect\Analyzer\ClassDescriptionRegistry;

interface ClassRegistryAwareExpression
{
    public function injectClassDescriptionRegistry(ClassDescriptionRegistry $classRegistry): void;
}
