<?php
declare(strict_types=1);

namespace Arkitect\Analyzer;

use Arkitect\Exceptions\ClassFileNotFoundException;

class ClassDependency
{
    private int $line;

    private FullyQualifiedClassName $FQCN;

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
