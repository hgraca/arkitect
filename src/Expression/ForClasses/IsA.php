<?php

declare(strict_types=1);

namespace Modulith\ArchCheck\Expression\ForClasses;

use Modulith\ArchCheck\Analyzer\ClassDescription;
use Modulith\ArchCheck\Expression\Description;
use Modulith\ArchCheck\Expression\Expression;
use Modulith\ArchCheck\Rules\Violation;
use Modulith\ArchCheck\Rules\ViolationMessage;
use Modulith\ArchCheck\Rules\Violations;

final class IsA implements Expression
{
    /** @var class-string[] */
    private $allowedFqcnList;

    /**
     * @param class-string ...$allowedFqcnList
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
                ViolationMessage::selfExplanatory($this->describe($theClass, $because))
            );

            $violations->add($violation);
        }
    }

    /**
     * @param class-string ...$allowedFqcnList
     */
    private function isA(ClassDescription $theClass, string ...$allowedFqcnList): bool
    {
        foreach ($allowedFqcnList as $allowedFqcn) {
            if (is_a($theClass->getFQCN(), $allowedFqcn, true)) {
                return true;
            }
        }

        return false;
    }
}
