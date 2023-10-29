<?php

declare(strict_types=1);

namespace Modulith\ArchCheck\Expression\ForClasses;

use Modulith\ArchCheck\Analyzer\ClassDescription;
use Modulith\ArchCheck\Expression\Description;
use Modulith\ArchCheck\Expression\Expression;
use Modulith\ArchCheck\Rules\Violation;
use Modulith\ArchCheck\Rules\ViolationMessage;
use Modulith\ArchCheck\Rules\Violations;

final class IsMapped implements Expression
{
    public const POSITIVE_DESCRIPTION = 'should exist in the list';

    /** @var array */
    private $list;

    public function __construct(array $list)
    {
        $this->list = array_flip($list);
    }

    public function describe(ClassDescription $theClass, string $because = ''): Description
    {
        return new Description(self::POSITIVE_DESCRIPTION, $because);
    }

    public function evaluate(ClassDescription $theClass, Violations $violations, string $because = ''): void
    {
        if (isset($this->list[$theClass->getFQCN()])) {
            return;
        }

        $violation = Violation::create(
            $theClass->getFQCN(),
            ViolationMessage::selfExplanatory($this->describe($theClass, $because))
        );

        $violations->add($violation);
    }
}
