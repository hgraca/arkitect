<?php
declare(strict_types=1);

namespace Modulith\ArchCheck\Test\Unit\Expressions\Boolean;

use Modulith\ArchCheck\Analyzer\ClassDescriptionBuilder;
use Modulith\ArchCheck\Expression\Boolean\Orx;
use Modulith\ArchCheck\Expression\ForClasses\Extend;
use Modulith\ArchCheck\Rules\Violations;
use PHPUnit\Framework\TestCase;

final class OrxTest extends TestCase
{
    public function test_it_should_return_no_violation_when_empty(): void
    {
        $or = new Orx();

        $classDescription = (new ClassDescriptionBuilder())
            ->setClassName('My\Class')
            ->setExtends('My\BaseClass', 10)
            ->build();

        $violations = new Violations();
        $or->evaluate($classDescription, $violations, 'because');

        self::assertEquals(0, $violations->count());
    }

    public function test_it_should_return_no_violation_on_success(): void
    {
        $or = new Orx(new Extend('My\BaseClass'), new Extend('Your\OtherClass'));

        $classDescription = (new ClassDescriptionBuilder())
            ->setClassName('My\Class')
            ->setExtends('My\BaseClass', 10)
            ->build();

        $violations = new Violations();
        $or->evaluate($classDescription, $violations, 'because');

        self::assertEquals(0, $violations->count());
    }
}
