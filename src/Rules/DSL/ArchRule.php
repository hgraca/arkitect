<?php
declare(strict_types=1);

namespace Modulith\ArchCheck\Rules\DSL;

use Modulith\ArchCheck\Analyzer\ClassDescription;
use Modulith\ArchCheck\Rules\Violations;

interface ArchRule
{
    public function check(ClassDescription $classDescription, Violations $violations): void;

    public function isRunOnlyThis(): bool;

    public function runOnlyThis(): self;
}
