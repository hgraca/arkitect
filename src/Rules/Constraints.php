<?php
declare(strict_types=1);

namespace Modulith\ArchCheck\Rules;

use Modulith\ArchCheck\Analyzer\ClassDescription;
use Modulith\ArchCheck\Expression\Expression;

class Constraints
{
    /** @var array */
    private $expressions = [];

    public function add(Expression $expression): void
    {
        $this->expressions[] = $expression;
    }

    public function checkAll(ClassDescription $classDescription, Violations $violations, string $because): void
    {
        /** @var Expression $expression */
        foreach ($this->expressions as $expression) {
            $expression->evaluate($classDescription, $violations, $because);
        }
    }
}
