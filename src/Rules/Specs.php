<?php
declare(strict_types=1);

namespace Modulith\ArchCheck\Rules;

use Modulith\ArchCheck\Analyzer\ClassDescription;
use Modulith\ArchCheck\Expression\Expression;

class Specs
{
    /** @var array */
    private $expressions = [];

    public function add(Expression $expression): void
    {
        $this->expressions[] = $expression;
    }

    public function allSpecsAreMatchedBy(ClassDescription $classDescription, string $because): bool
    {
        /** @var Expression $spec */
        foreach ($this->expressions as $spec) {
            $violations = new Violations();
            $spec->evaluate($classDescription, $violations, $because);

            if ($violations->count() > 0) {
                return false;
            }
        }

        return true;
    }
}
