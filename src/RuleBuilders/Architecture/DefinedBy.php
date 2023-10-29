<?php
declare(strict_types=1);

namespace Modulith\ArchCheck\RuleBuilders\Architecture;

use Modulith\ArchCheck\Expression\Expression;

interface DefinedBy
{
    /** @return Component&Where */
    public function definedBy(string $selector);

    /** @return Component&Where */
    public function definedByExpression(Expression $selector);
}
