<?php
declare(strict_types=1);

namespace Modulith\ArchCheck\Rules\DSL;

use Modulith\ArchCheck\Expression\Expression;

interface AndThatShouldParser
{
    public function andThat(Expression $expression): self;

    public function should(Expression $expression): BecauseParser;
}
