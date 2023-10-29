<?php
declare(strict_types=1);

namespace Modulith\ArchCheck\Test\Unit\Expressions\ForClasses;

use Modulith\ArchCheck\Analyzer\ClassDescription;
use Modulith\ArchCheck\Expression\ForClasses\NotHaveNameMatching;
use Modulith\ArchCheck\Rules\Violations;
use PHPUnit\Framework\TestCase;

class NotHaveNameMatchingTest extends TestCase
{
    public function test_check_class_name_match_create_violation_if_name_matches(): void
    {
        $expression = new NotHaveNameMatching('*Class');

        $myClass = ClassDescription::getBuilder('\App\MyClass')->build();

        $violations = new Violations();
        $because = 'we want to add this rule for our software';
        $expression->evaluate($myClass, $violations, $because);
        self::assertEquals(1, $violations->count());
        $this->assertEquals(
            "should not have a name that matches *Class\nbecause we want to add this rule for our software",
            $expression->describe($myClass, $because)->toString()
        );
    }

    public function test_show_violation_when_class_name_does_not_match(): void
    {
        $expression = new NotHaveNameMatching('*GoodName*');

        $badClass = ClassDescription::getBuilder('\App\BadNameClass')->build();

        $because = 'we want to add this rule for our software';
        $violations = new Violations();
        $expression->evaluate($badClass, $violations, $because);
        self::assertEquals(0, $violations->count());
    }
}
