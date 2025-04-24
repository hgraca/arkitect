<?php

declare(strict_types=1);

namespace Arkitect\Tests\Unit\Expressions\ForClasses;

use Arkitect\Analyzer\ClassDescription;
use Arkitect\Analyzer\ClassDescriptionCollection;
use Arkitect\Analyzer\ClassDescriptionRegistry;
use Arkitect\Analyzer\FileParserFactory;
use Arkitect\Expression\ForClasses\IsA;
use Arkitect\Rules\Violations;
use Arkitect\Tests\Unit\Expressions\ForClasses\DummyClasses\Banana;
use Arkitect\Tests\Unit\Expressions\ForClasses\DummyClasses\CavendishBanana;
use Arkitect\Tests\Unit\Expressions\ForClasses\DummyClasses\Dog;
use Arkitect\Tests\Unit\Expressions\ForClasses\DummyClasses\DwarfCavendishBanana;
use Arkitect\Tests\Unit\Expressions\ForClasses\DummyClasses\FruitInterface;
use PHPUnit\Framework\TestCase;

final class IsATest extends TestCase
{
    public function test_it_should_have_no_violation_when_it_implements(): void
    {
        $classUnderTest = CavendishBanana::class;
        $interface = FruitInterface::class;
        $isA = new IsA($interface);
        $isA->injectClassDescriptionRegistry(
            ClassDescriptionRegistry::new(
                $this->getClassDescriptionCollection(CavendishBanana::class, Banana::class, $interface)
            )
        );

        $violations = new Violations();
        $isA->evaluate($this->getClassDescription($classUnderTest), $violations, '');

        self::assertEquals(0, $violations->count());
    }

    public function test_it_should_have_no_violation_when_it_extends(): void
    {
        $classUnderTest = DwarfCavendishBanana::class;
        $parent = Banana::class;
        $isA = new IsA($parent);
        $isA->injectClassDescriptionRegistry(
            ClassDescriptionRegistry::new(
                $this->getClassDescriptionCollection($classUnderTest, CavendishBanana::class, Banana::class)
            )
        );

        $violations = new Violations();
        $isA->evaluate($this->getClassDescription($classUnderTest), $violations, '');

        self::assertEquals(0, $violations->count());
    }

    public function test_it_should_have_violation_if_it_doesnt_extend_nor_implement(): void
    {
        $interface = FruitInterface::class;
        $class = Banana::class;
        $classUnderTest = Dog::class;
        $isA = new IsA($class, $interface);
        $classDescription = $this->getClassDescription($classUnderTest);

        $violations = new Violations();
        $isA->evaluate($classDescription, $violations, '');

        self::assertEquals(1, $violations->count());
        self::assertEquals(
            "should inherit from one of: $class, $interface",
            $isA->describe($classDescription, '')->toString()
        );
    }

    private function getClassDescriptionCollection(string ...$fqcnList): ClassDescriptionCollection
    {
        $collection = new ClassDescriptionCollection();
        foreach ($fqcnList as $fqcn) {
            $collection->add($this->getClassDescription($fqcn));
        }

        return $collection;
    }

    private function getClassDescription(string $fqcn): ClassDescription
    {
        $reflector = new \ReflectionClass($fqcn);
        $filename = $reflector->getFileName();

        $fileParser = FileParserFactory::createFileParser();
        $fileParser->parse(file_get_contents($filename), $filename);

        return $fileParser->getClassDescriptions()->reset();
    }
}
