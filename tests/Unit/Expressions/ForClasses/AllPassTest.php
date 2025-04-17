<?php

declare(strict_types=1);

namespace Arkitect\Tests\Unit\Expressions\ForClasses;

use Arkitect\Analyzer\ClassDescriptionBuilder;
use Arkitect\Expression\ForClasses\AllPass;
use Arkitect\Rules\Violations;
use PHPUnit\Framework\TestCase;

final class AllPassTest extends TestCase
{
    public function test_it_always_passes(): void
    {
        $expression = new AllPass();

        $classDescription = (new ClassDescriptionBuilder())
            ->setFilePath('src/Foo.php')
            ->setClassName('HappyIsland\Myclass')
            ->build();

        $because = 'we want it to always pass';
        $violations = new Violations();
        $expression->evaluate($classDescription, $violations, $because);

        self::assertEquals(0, $violations->count());
        self::assertEquals(
            AllPass::DESCRIPTION." because {$because}",
            $expression->describe($classDescription, $because)->toString()
        );
    }
}
