<?php
declare(strict_types=1);

namespace Modulith\ArchCheck\Test\Unit\Expressions\ForClasses;

use Modulith\ArchCheck\Analyzer\ClassDescription;
use Modulith\ArchCheck\Expression\ForClasses\MatchOneOfTheseNames;
use Modulith\ArchCheck\Rules\Violations;
use PHPUnit\Framework\TestCase;

class MatchOneOfTheseNamesTest extends TestCase
{
    public function test_check_class_name_match(): void
    {
        $expression = new MatchOneOfTheseNames(['*BadNameClass', '*Class']);

        $goodClass = ClassDescription::getBuilder('\App\MyClass')->build();
        $because = 'we want to add this rule for our software';
        $violations = new Violations();
        $expression->evaluate($goodClass, $violations, $because);
        self::assertEquals(0, $violations->count());
    }

    public function test_show_violation_when_class_name_does_not_match(): void
    {
        $expression = new MatchOneOfTheseNames(['*BetterName*', '*GoodName*']);

        $badClass = ClassDescription::getBuilder('\App\BadNameClass')->build();
        $because = 'we want to add this rule for our software';
        $violations = new Violations();
        $expression->evaluate($badClass, $violations, $because);
        self::assertNotEquals(0, $violations->count());
        $this->assertEquals(
            "should have a name that matches *BetterName*, *GoodName*\nbecause we want to add this rule for our software",
            $expression->describe($badClass, $because)->toString()
        );
    }
}
