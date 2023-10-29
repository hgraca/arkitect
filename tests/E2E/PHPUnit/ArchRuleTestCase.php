<?php
declare(strict_types=1);

namespace Modulith\ArchCheck\Test\E2E\PHPUnit;

use Modulith\ArchCheck\ClassSet;
use Modulith\ArchCheck\PHPUnit\ArchRuleCheckerConstraintAdapter;
use Modulith\ArchCheck\Rules\DSL\ArchRule;
use PHPUnit\Framework\TestCase;

class ArchRuleTestCase extends TestCase
{
    public static function assertArchRule(ArchRule $rule, ClassSet $set): void
    {
        $constraint = new ArchRuleCheckerConstraintAdapter($set);

        static::assertThat($rule, $constraint);
    }
}
