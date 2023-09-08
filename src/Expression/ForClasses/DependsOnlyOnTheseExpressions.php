<?php

declare(strict_types=1);

namespace Arkitect\Expression\ForClasses;

use Arkitect\Analyzer\ClassDependency;
use Arkitect\Analyzer\ClassDescription;
use Arkitect\Analyzer\FileParser;
use Arkitect\Analyzer\FileParserFactory;
use Arkitect\CLI\TargetPhpVersion;
use Arkitect\Expression\Description;
use Arkitect\Expression\Expression;
use Arkitect\Rules\Violation;
use Arkitect\Rules\ViolationMessage;
use Arkitect\Rules\Violations;

class DependsOnlyOnTheseExpressions implements Expression
{
    /** @var FileParser */
    private $fileParser;

    /** @var Expression[] */
    private $expressions;

    public function __construct(Expression ...$expressions)
    {
        $this->fileParser = FileParserFactory::createFileParser(TargetPhpVersion::create(null));
        $this->expressions = $expressions;
    }

    public function describe(ClassDescription $theClass, string $because): Description
    {
        return new Description(
            "should depend only on classes in one of the given expressions",
            $because
        );
    }

    public function evaluate(ClassDescription $theClass, Violations $violations, string $because): void
    {
        $dependencies = $theClass->getDependencies();

        foreach ($dependencies as $dependency) {
            if (
                '' === $dependency->getFQCN()->namespace()
                || $theClass->namespaceMatches($dependency->getFQCN()->namespace())
            ) {
                continue;
            }


            $dependencyClassDescription = $this->getDependencyClassDescription($dependency);
            if ($dependencyClassDescription === null) {
                return;
            }

            if (!$this->matchesAnyOfTheExpressions($dependencyClassDescription)) {
                $violations->add(
                    Violation::create(
                        $theClass->getFQCN(),
                    ViolationMessage::withDescription(
                        $this->describe($theClass, $because),
                        "The dependency '" . $dependencyClassDescription->getFQCN() . "' violated the expression: \n"
                        . '['.$this->describeDependencyRequirement($dependencyClassDescription, '').']'
                    )
                )
                );
            }
        }
    }

    private function describeDependencyRequirement(ClassDescription $theDependency): string
    {
        $expressionsDescriptions = [];
        foreach ($this->expressions as $expression) {
            $expressionsDescriptions[] = $expression->describe($theDependency, '')->toString();
        }

        return '['.implode('] OR [', array_unique($expressionsDescriptions)).']';
    }

    private function matchesAnyOfTheExpressions(ClassDescription $dependencyClassDescription): bool
    {
        foreach ($this->expressions as $expression) {
            $newViolations = new Violations();
            $expression->evaluate($dependencyClassDescription, $newViolations, '');
            if (0 === $newViolations->count()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ClassDependency $dependency
     */
    private function getDependencyClassDescription($dependency): ?ClassDescription
    {
        $reflector = new \ReflectionClass($dependency->getFQCN()->toString());
        $filename = $reflector->getFileName();
        if ($filename === false) {
            return null;
        }
        $this->fileParser->parse(\file_get_contents($filename), $filename);
        $classDescriptionList = $this->fileParser->getClassDescriptions();

        return \array_pop($classDescriptionList);
    }
}
