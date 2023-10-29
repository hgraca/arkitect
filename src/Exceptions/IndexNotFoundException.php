<?php
declare(strict_types=1);

namespace Modulith\ArchCheck\Exceptions;

class IndexNotFoundException extends \Exception
{
    public function __construct(int $index)
    {
        parent::__construct(sprintf('Index not found %d', $index));
    }
}
