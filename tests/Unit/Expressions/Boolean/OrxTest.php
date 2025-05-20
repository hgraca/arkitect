<?php
declare(strict_types=1);

namespace Arkitect\Tests\Unit\Expressions\Boolean;

use Arkitect\Analyzer\ClassDescriptionBuilder;
use Arkitect\Expression\Boolean\Orx;
use Arkitect\Expression\ForClasses\Extend;
use Arkitect\Rules\Violations;
use PHPUnit\Framework\TestCase;

final class OrxTest extends TestCase
{
    public function test_it_should_return_no_violation_if_no_expression_provided(): void
    {
        $or = new Orx();

        $classDescription = (new ClassDescriptionBuilder())
            ->setFilePath('src/Foo.php')
            ->setClassName('My\Class')
            ->addExtends('My\BaseClass', 10)
            ->build();

        $violations = new Violations();
        $or->evaluate($classDescription, $violations, 'because');

        self::assertEquals(0, $violations->count());
    }

    public function test_it_should_return_no_violation_on_success(): void
    {
        $or = new Orx(new Extend('My\BaseClass'), new Extend('Your\OtherClass'));

        $classDescription = (new ClassDescriptionBuilder())
            ->setFilePath('src/Foo.php')
            ->setClassName('My\Class')
            ->addExtends('My\BaseClass', 10)
            ->build();

        $violations = new Violations();
        $or->evaluate($classDescription, $violations, 'because');

        self::assertEquals(0, $violations->count());
    }

    public function test_it_should_return_violation_on_failure(): void
    {
        $or = new Orx(new Extend('My\NotExtendedBaseClass'));

        $classDescription = (new ClassDescriptionBuilder())
            ->setFilePath('src/Foo.php')
            ->setClassName('My\Class')
            ->addExtends('My\BaseClass', 10)
            ->build();

        $violations = new Violations();
        $or->evaluate($classDescription, $violations, 'because');

        self::assertEquals(1, $violations->count());
    }
}
