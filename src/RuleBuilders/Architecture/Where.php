<?php
declare(strict_types=1);

namespace Modulith\ArchCheck\RuleBuilders\Architecture;

interface Where
{
    /** @return ShouldNotDependOnAnyComponent&ShouldOnlyDependOnComponents&MayDependOnComponents&MayDependOnAnyComponent&MustNotDependOnComponents */
    public function where(string $componentName);
}
