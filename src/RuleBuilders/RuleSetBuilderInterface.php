<?php

declare(strict_types=1);

namespace Arkitect\RuleBuilders;

use Arkitect\Rules\DSL\ArchRule;

interface RuleSetBuilderInterface
{
    /**
     * @return array<ArchRule>
     */
    public function build(): array;
}
