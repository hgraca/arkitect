<?php

declare(strict_types=1);

namespace Arkitect\Tests\Unit\Analyzer;

use Arkitect\Analyzer\ClassDescription;
use Arkitect\Analyzer\ClassDescriptionBuilder;
use PHPUnit\Framework\TestCase;

class ClassDescriptionTest extends TestCase
{
    private ClassDescriptionBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = ClassDescription::getBuilder('Fruit\Banana', 'src/Foo.php');
    }

    public function test_should_return_file_path(): void
    {
        $cd = $this->builder->build();

        self::assertEquals('src/Foo.php', $cd->getFilePath());
    }

    public function test_should_return_true_if_there_class_is_in_namespace(): void
    {
        $cd = $this->builder->build();

        self::assertTrue($cd->namespaceMatches('Fruit'));
    }

    public function test_should_return_name(): void
    {
        $cd = $this->builder->build();

        self::assertEquals('Banana', $cd->getName());
    }

    public function test_should_return_true_if_there_class_is_in_namespace_array(): void
    {
        $cd = $this->builder->build();

        self::assertTrue($cd->namespaceMatchesOneOfTheseNamespaces(['Fruit']));
    }

    public function test_should_return_true_if_there_class_is_in_namespace_string_list(): void
    {
        $cd = $this->builder->build();

        self::assertTrue($cd->namespaceMatchesOneOfTheseStringNamespaces('Fruit'));
    }

    public function test_should_return_true_if_is_annotated_with(): void
    {
        $cd = $this->builder
            ->addDocBlock('/**
 * @psalm-immutable
 */')
            ->build();

        self::assertTrue($cd->containsDocBlock('@psalm-immutable'));
    }

    public function test_should_return_false_if_not_annotated_with(): void
    {
        $cd = $this->builder
            ->addDocBlock('/**
 * @psalm-immutable
 */')
            ->build();

        self::assertFalse($cd->containsDocBlock('@another-annotation'));
    }

    public function test_should_return_true_if_has_attribute(): void
    {
        $cd = $this->builder
            ->addAttribute('FooAttr', 27)
            ->build();

        self::assertTrue($cd->hasAttribute('FooAttr'));
        self::assertTrue($cd->hasAttribute('Foo*'));
    }

    public function test_should_return_false_if_not_has_attribute(): void
    {
        $cd = $this->builder
            ->addAttribute('FooAttr', 27)
            ->build();

        self::assertFalse($cd->hasAttribute('Bar'));
    }
}
