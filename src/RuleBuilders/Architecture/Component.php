<?php
declare(strict_types=1);

namespace Modulith\ArchCheck\RuleBuilders\Architecture;

interface Component
{
    public function component(string $name): DefinedBy;
}
