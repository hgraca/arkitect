<?php

declare(strict_types=1);

namespace Modulith\ArchCheck\Expression;

class Description
{
    /** @var string */
    private $description;

    public function __construct(string $description, string $because = '')
    {
        $this->description = $description;

        if ('' !== $because) {
            $this->description .= "\nbecause $because";
        }
    }

    public function toString(): string
    {
        return $this->description;
    }
}
