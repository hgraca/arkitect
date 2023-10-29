<?php
declare(strict_types=1);

namespace Modulith\ArchCheck\RuleBuilders\Architecture;

use Modulith\ArchCheck\Rules\DSL\ArchRule;

interface Rules
{
    /** @return iterable<array-key, ArchRule> */
    public function rules(string $because = 'of component architecture'): iterable;
}
