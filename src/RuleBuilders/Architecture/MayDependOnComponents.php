<?php
declare(strict_types=1);

namespace Modulith\ArchCheck\RuleBuilders\Architecture;

interface MayDependOnComponents
{
    /**
     * May depend on the specified components, plus itself.
     *
     * @param string[] $componentNames
     *
     * @return Where&Rules
     */
    public function mayDependOnComponents(string ...$componentNames);
}
