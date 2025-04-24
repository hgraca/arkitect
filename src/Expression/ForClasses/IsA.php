<?php

declare(strict_types=1);

namespace Arkitect\Expression\ForClasses;

use Arkitect\Analyzer\ClassDescription;
use Arkitect\Analyzer\ClassDescriptionRegistry;
use Arkitect\Expression\ClassRegistryAwareExpression;
use Arkitect\Expression\Description;
use Arkitect\Expression\Expression;
use Arkitect\Rules\Violation;
use Arkitect\Rules\ViolationMessage;
use Arkitect\Rules\Violations;

final class IsA implements Expression, ClassRegistryAwareExpression
{
    /** @var array<class-string> */
    private $allowedFqcnList;

    /**
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private ClassDescriptionRegistry $classDescriptionRegistry;

    /**
     * @param array<class-string> $allowedFqcnList
     */
    public function __construct(string ...$allowedFqcnList)
    {
        $this->allowedFqcnList = $allowedFqcnList;
    }

    public function describe(ClassDescription $theClass, string $because = ''): Description
    {
        $allowedFqcnList = implode(', ', $this->allowedFqcnList);

        return new Description("should inherit from one of: $allowedFqcnList", $because);
    }

    public function evaluate(ClassDescription $theClass, Violations $violations, string $because = ''): void
    {
        if (!$this->isA($theClass, ...$this->allowedFqcnList)) {
            $violation = Violation::create(
                $theClass->getFQCN(),
                ViolationMessage::selfExplanatory($this->describe($theClass, $because)),
                $theClass->getFilePath()
            );

            $violations->add($violation);
        }
    }

    public function injectClassDescriptionRegistry(ClassDescriptionRegistry $classRegistry): void
    {
        $this->classDescriptionRegistry = $classRegistry;
    }

    /**
     * @param array<class-string> $allowedFqcnList
     */
    private function isA(ClassDescription $theClass, string ...$allowedFqcnList): bool
    {
        $parentList = $theClass->getExtends();
        $implementationList = $theClass->getInterfaces();

        foreach ($allowedFqcnList as $allowedFqcn) {
            foreach ($parentList as $parent) {
                $parentDescription = $this->classDescriptionRegistry->getByClass($parent);
                if ($parent->matches($allowedFqcn) || $this->isA($parentDescription, ...$allowedFqcnList)) {
                    return true;
                }
            }
            foreach ($implementationList as $interface) {
                $interfaceDescription = $this->classDescriptionRegistry->getByClass($interface);
                if ($interface->matches($allowedFqcn) || $this->isA($interfaceDescription, ...$allowedFqcnList)) {
                    return true;
                }
            }
        }

        return false;
    }
}
