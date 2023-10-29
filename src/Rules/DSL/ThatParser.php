<?php
declare(strict_types=1);

namespace Modulith\ArchCheck\Rules\DSL;

use Modulith\ArchCheck\Expression\Expression;

interface ThatParser
{
    public function except(string ...$classesToBeExcluded): self;

    public function exceptExpression(Expression ...$classesToBeExcluded): self;

    public function that(Expression $expression): AndThatShouldParser;
}
