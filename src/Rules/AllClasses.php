<?php
declare(strict_types=1);

namespace Modulith\ArchCheck\Rules;

use Modulith\ArchCheck\Expression\Expression;
use Modulith\ArchCheck\Rules\DSL\AndThatShouldParser;
use Modulith\ArchCheck\Rules\DSL\ThatParser;

class AllClasses implements ThatParser
{
    /** @var RuleBuilder */
    protected $ruleBuilder;

    public function __construct()
    {
        $this->ruleBuilder = new RuleBuilder();
    }

    public function that(Expression $expression): AndThatShouldParser
    {
        $this->ruleBuilder->addThat($expression);

        return new AndThatShould($this->ruleBuilder);
    }

    public function except(string ...$classesToBeExcluded): ThatParser
    {
        $this->ruleBuilder->classesToBeExcluded(...$classesToBeExcluded);

        return $this;
    }

    public function exceptExpression(Expression ...$classesToBeExcluded): ThatParser
    {
        $this->ruleBuilder->classesToBeExcludedByExpression(...$classesToBeExcluded);

        return $this;
    }
}
