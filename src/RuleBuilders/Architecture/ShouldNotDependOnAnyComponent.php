<?php
declare(strict_types=1);

namespace Modulith\ArchCheck\RuleBuilders\Architecture;

interface ShouldNotDependOnAnyComponent
{
    /** @return Where&Rules */
    public function shouldNotDependOnAnyComponent();
}
