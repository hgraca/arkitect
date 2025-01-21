<?php
declare(strict_types=1);

namespace Modulith\ArchCheck\Analyzer;

use Modulith\ArchCheck\Exceptions\ClassFileNotFoundException;

class ClassDependency
{
    /** @var int */
    private $line;

    /** @var FullyQualifiedClassName */
    private $FQCN;

    public function __construct(string $FQCN, int $line)
    {
        $this->line = $line;
        $this->FQCN = FullyQualifiedClassName::fromString($FQCN);
    }

    public function matches(string $pattern): bool
    {
        return $this->FQCN->matches($pattern);
    }

    public function matchesOneOf(string ...$patterns): bool
    {
        foreach ($patterns as $pattern) {
            if ($this->FQCN->matches($pattern)) {
                return true;
            }
        }

        return false;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getFQCN(): FullyQualifiedClassName
    {
        return $this->FQCN;
    }

    /**
     * @throws ClassFileNotFoundException
     * @throws \ReflectionException
     */
    public function getClassDescription(): ClassDescription
    {
        /** @var class-string $dependencyFqcn */
        $dependencyFqcn = $this->getFQCN()->toString();
        $reflector = new \ReflectionClass($dependencyFqcn);
        $filename = $reflector->getFileName();
        if (false === $filename) {
            if (class_exists($dependencyFqcn)) {
                return $this->createDescriptionOfNativeClass($reflector);
            }
            throw new ClassFileNotFoundException($dependencyFqcn);
        }

        $fileParser = FileParserFactory::createFileParser();
        $fileParser->parse(file_get_contents($filename), $filename);
        $classDescriptionList = $fileParser->getClassDescriptions();

        return array_pop($classDescriptionList);
    }

    private function createDescriptionOfNativeClass(\ReflectionClass $reflector): ClassDescription
    {
        $interfaces = [];
        foreach ($reflector->getInterfaces() as $interface) {
            $interfaces[] = FullyQualifiedClassName::fromString($interface->getName());
        }

        return new ClassDescription(
            FullyQualifiedClassName::fromString($reflector->getName()),
            [], // we can't know this without a file, but we also don't care as these are built in classes
            $interfaces,
            null,
            $reflector->isFinal(),
            $reflector->isAbstract(),
            $reflector->isInterface(),
            $reflector->isTrait(),
            $reflector->isEnum(),
        );
    }
}
