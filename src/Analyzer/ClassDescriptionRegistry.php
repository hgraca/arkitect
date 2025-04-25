<?php

declare(strict_types=1);

namespace Arkitect\Analyzer;

use Arkitect\Expression\ClassRegistryAwareExpression;
use Arkitect\Expression\Expression;

/**
 * @implements \IteratorAggregate<ClassDescription>
 */
final class ClassDescriptionRegistry implements \IteratorAggregate
{
    private static ?self $self = null;

    private ClassDescriptionCollection $classDescriptionCollection;

    private function __construct(?ClassDescriptionCollection $classDescriptionCollection = null)
    {
        $this->classDescriptionCollection = $classDescriptionCollection ?? new ClassDescriptionCollection();
        self::$self = $this;
    }

    public static function new(?ClassDescriptionCollection $classDescriptionCollection = null): self
    {
        return new self($classDescriptionCollection);
    }

    public static function get(?ClassDescriptionCollection $classDescriptionCollection = null): self
    {
        return self::$self ?? new self($classDescriptionCollection);
    }

    public function injectInto(Expression ...$classesToBeExcluded): void
    {
        foreach ($classesToBeExcluded as $expression) {
            if ($expression instanceof ClassRegistryAwareExpression) {
                $expression->injectClassDescriptionRegistry($this);
            }
        }
    }

    /**
     * @return \Iterator<array-key, ClassDescription>
     */
    public function getIterator(): \Iterator
    {
        return $this->classDescriptionCollection->getIterator();
    }

    public function add(ClassDescription ...$classDescriptionList): void
    {
        foreach ($classDescriptionList as $classDescription) {
            $this->classDescriptionCollection->add($classDescription);
        }
    }

    public function addCollection(ClassDescriptionCollection $classDescriptionCollection): void
    {
        foreach ($classDescriptionCollection as $classDescription) {
            $this->add($classDescription);
        }
    }

    public function getByClass(FullyQualifiedClassName $fullyQualifiedClassName): ClassDescription
    {
        if ($this->classDescriptionCollection->hasClass($fullyQualifiedClassName)) {
            return $this->classDescriptionCollection->getByClass($fullyQualifiedClassName);
        }

        if (!class_exists($fullyQualifiedClassName->toString(), false)) {
            throw new UnknownClass("The class {$fullyQualifiedClassName->toString()} was requested but it has not been parsed ".'and is not a native class.');
        }

        $classDescription = $this->createDescriptionOfNativeClass(
            new \ReflectionClass($fullyQualifiedClassName->toString())
        );

        $this->classDescriptionCollection->add($classDescription);

        return $classDescription;
    }

    public function getByFile(string $path): ClassDescription
    {
        if ($this->classDescriptionCollection->hasFile($path)) {
            return $this->classDescriptionCollection->getByFile($path);
        }

        throw new UnknownClass("Unknown class from file '{$path}'");
    }

    public function hasFile(string $path): bool
    {
        return isset($this->classDescriptionListByFile[$path]);
    }

    private function createDescriptionOfNativeClass(\ReflectionClass $reflector): ClassDescription
    {
        /** @var class-string $enumRootInterface */
        $enumRootInterface = 'UnitEnum';
        $isEnum = interface_exists($enumRootInterface) && $reflector->implementsInterface($enumRootInterface);

        $classDescription = ClassDescription::getBuilder($reflector->getName(), '')
            ->setFinal($reflector->isFinal())
            ->setAbstract($reflector->isAbstract())
            ->setInterface($reflector->isInterface())
            ->setTrait($reflector->isTrait())
            ->setEnum($isEnum);

        $implementedInterfaceLit = array_map(
            fn (\ReflectionClass $interface) => FullyQualifiedClassName::fromString($interface->getName()),
            $reflector->getInterfaces()
        );
        foreach ($implementedInterfaceLit as $i => $interface) {
            $classDescription->addInterface($interface->toString(), (int) $i + 10);
        }

        return $classDescription->build();
    }
}
