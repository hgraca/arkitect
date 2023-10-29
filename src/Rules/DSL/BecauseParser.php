<?php
declare(strict_types=1);

namespace Modulith\ArchCheck\Rules\DSL;

interface BecauseParser
{
    public function because(string $reason): ArchRule;
}
