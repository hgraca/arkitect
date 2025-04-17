<?php

declare(strict_types=1);

namespace Arkitect\Tests\Unit\Expressions\ForClasses;

use Arkitect\Analyzer\ClassDescriptionBuilder;
use Arkitect\Expression\ForClasses\NonePass;
use Arkitect\Rules\Violations;
use PHPUnit\Framework\TestCase;

final class NonePassTest extends TestCase
{
    public function test_it_always_passes(): void
    {
        $expression = new NonePass();

        $classDescription = (new ClassDescriptionBuilder())
            ->setFilePath('src/Foo.php')
            ->setClassName('HappyIsland\Myclass')
            ->build();

        $because = 'we want it to always have violations';
        $violations = new Violations();
        $expression->evaluate($classDescription, $violations, $because);

        self::assertEquals(1, $violations->count());
        self::assertEquals(
            NonePass::DESCRIPTION." because {$because}",
            $expression->describe($classDescription, $because)->toString()
        );
    }
}
