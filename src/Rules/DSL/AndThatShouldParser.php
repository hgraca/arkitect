<?php
declare(strict_types=1);

namespace Arkitect\Rules\DSL;

use Arkitect\Expression\Expression;

interface AndThatShouldParser
{
    /**
     * @deprecated This is not working as expected, use `->that(new Andx(...))` instead.
     */
    public function andThat(Expression $expression): self;

    public function should(Expression $expression): BecauseParser;
}
