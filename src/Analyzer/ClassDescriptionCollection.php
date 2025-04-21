<?php

declare(strict_types=1);

namespace Arkitect\Analyzer;

/**
 * @implements \IteratorAggregate<ClassDescription>
 */
final class ClassDescriptionCollection implements \IteratorAggregate
{
    /**
     * @var array<class-string, ClassDescription>
     */
    private $classDescriptionListByClass = [];

    /**
     * @var array<string, ClassDescription>
     */
    private $classDescriptionListByFile = [];

    public function __construct(ClassDescription ...$classDescriptionList)
    {
        foreach ($classDescriptionList as $classDescription) {
            $this->add($classDescription);
        }
    }

    /**
     * @return \Iterator<array-key, ClassDescription>
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->classDescriptionListByClass);
    }

    public function add(ClassDescription $classDescription): void
    {
        $this->classDescriptionListByClass[$classDescription->getFQCN()] = $classDescription;
        if ('' !== $classDescription->getFilePath()) { // because native classes don't have a file path
            $this->classDescriptionListByFile[$classDescription->getFilePath()] = $classDescription;
        }
    }

    public function addCollection(self $classDescriptionRegistry): void
    {
        foreach ($classDescriptionRegistry as $classDescription) {
            $this->add($classDescription);
        }
    }

    public function merge(self $classDescriptionRegistry): self
    {
        return new self(
            ...array_values($this->classDescriptionListByClass),
            ...array_values($classDescriptionRegistry->classDescriptionListByClass)
        );
    }

    public function getByClass(FullyQualifiedClassName $fullyQualifiedClassName): ClassDescription
    {
        return $this->classDescriptionListByClass[$fullyQualifiedClassName->toString()];
    }

    public function getByFile(string $path): ClassDescription
    {
        return $this->classDescriptionListByFile[$path];
    }

    public function hasClass(FullyQualifiedClassName $fullyQualifiedClassName): bool
    {
        return isset($this->classDescriptionListByClass[$fullyQualifiedClassName->toString()]);
    }

    public function hasFile(string $path): bool
    {
        return isset($this->classDescriptionListByFile[$path]);
    }

    public function reset(): ClassDescription
    {
        return reset($this->classDescriptionListByClass);
    }

    public function isEmpty(): bool
    {
        return empty($this->classDescriptionListByClass);
    }
}
