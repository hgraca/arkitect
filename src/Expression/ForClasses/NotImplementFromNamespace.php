<?php

declare(strict_types=1);

namespace Arkitect\Expression\ForClasses;

use Arkitect\Analyzer\ClassDescription;
use Arkitect\Expression\Description;
use Arkitect\Expression\Expression;
use Arkitect\Rules\Violation;
use Arkitect\Rules\ViolationMessage;
use Arkitect\Rules\Violations;

class NotImplementFromNamespace implements Expression
{
    /** @var string */
    private $namespace;

    /** @var array<string> */
    private array $exclusionList;

    public function __construct(string $namespace, array $exclusionList = [])
    {
        $this->namespace = $namespace;
        $this->exclusionList = $exclusionList;
    }

    public function describe(ClassDescription $theClass, string $because = ''): Description
    {
        $exclusionDescription = [] !== $this->exclusionList
            ? ', except for ['.implode(', ', $this->exclusionList).']'
            : '';

        return new Description(
            "should not implement from namespace {$this->namespace}{$exclusionDescription}",
            $because
        );
    }

    public function evaluate(ClassDescription $theClass, Violations $violations, string $because = ''): void
    {
        if ($theClass->isInterface() || $theClass->isTrait()) {
            return;
        }

        $implementedAndNotExcludedInterfaceList = [];
        foreach ($theClass->getInterfaces() as $implementedInterface) {
            if (!$this->isExcluded($implementedInterface->toString())) {
                $implementedAndNotExcludedInterfaceList[] = $implementedInterface;
            }
        }

        foreach ($implementedAndNotExcludedInterfaceList as $implementedInterface) {
            if (str_starts_with($implementedInterface->toString(), $this->namespace)) {
                $violation = Violation::create(
                    $theClass->getFQCN(),
                    ViolationMessage::selfExplanatory($this->describe($theClass, $because)),
                    $theClass->getFilePath(),
                );
                $violations->add($violation);
            }
        }
    }

    private function isExcluded(string $implementedInterface): bool
    {
        foreach ($this->exclusionList as $excludedNamespace) {
            if (
                $implementedInterface === $excludedNamespace
                || str_starts_with($implementedInterface, rtrim($excludedNamespace, '\\').'\\')
            ) {
                return true;
            }
        }

        return false;
    }
}
