<?php
declare(strict_types=1);

namespace Modulith\ArchCheck\Expression\ForClasses;

use Modulith\ArchCheck\Analyzer\ClassDescription;
use Modulith\ArchCheck\Expression\Description;
use Modulith\ArchCheck\Expression\Expression;
use Modulith\ArchCheck\Rules\Violation;
use Modulith\ArchCheck\Rules\ViolationMessage;
use Modulith\ArchCheck\Rules\Violations;

final class HaveAttribute implements Expression
{
    /** @var string */
    private $attribute;

    public function __construct(string $attribute)
    {
        $this->attribute = $attribute;
    }

    public function describe(ClassDescription $theClass, string $because = ''): Description
    {
        return new Description("should have the attribute {$this->attribute}", $because);
    }

    public function evaluate(ClassDescription $theClass, Violations $violations, string $because = ''): void
    {
        if ($theClass->hasAttribute($this->attribute)) {
            return;
        }

        $violations->add(
            Violation::create(
                $theClass->getFQCN(),
                ViolationMessage::selfExplanatory($this->describe($theClass, $because))
            )
        );
    }
}
