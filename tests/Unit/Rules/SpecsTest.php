<?php

declare(strict_types=1);

namespace Modulith\ArchCheck\Test\Unit\Rules;

use Modulith\ArchCheck\Analyzer\ClassDependency;
use Modulith\ArchCheck\Analyzer\ClassDescription;
use Modulith\ArchCheck\Expression\ForClasses\HaveNameMatching;
use Modulith\ArchCheck\Rules\Specs;
use PHPUnit\Framework\TestCase;

class SpecsTest extends TestCase
{
    public function test_return_false_if_not_all_specs_are_matched(): void
    {
        $specStore = new Specs();
        $specStore->add(new HaveNameMatching('Foo'));

        $classDescription = ClassDescription::getBuilder('MyNamespace\HappyIsland')->build();
        $because = 'we want to add this rule for our software';

        $this->assertFalse($specStore->allSpecsAreMatchedBy($classDescription, $because));
    }

    public function test_return_true_if_all_specs_are_matched(): void
    {
        $specStore = new Specs();
        $specStore->add(new HaveNameMatching('Happy*'));

        $classDescription = ClassDescription::getBuilder('MyNamespace\HappyIsland')
            ->addDependency(new ClassDependency('Foo', 100))
            ->build();
        $because = 'we want to add this rule for our software';

        $this->assertTrue($specStore->allSpecsAreMatchedBy($classDescription, $because));
    }
}
