<?php
declare(strict_types=1);

namespace Modulith\ArchCheck\RuleBuilders\Architecture;

interface MayDependOnAnyComponent
{
    /** @return Where&Rules */
    public function mayDependOnAnyComponent();
}
