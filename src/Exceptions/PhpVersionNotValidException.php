<?php
declare(strict_types=1);

namespace Modulith\ArchCheck\Exceptions;

class PhpVersionNotValidException extends \Exception
{
    public function __construct(string $phpVersion)
    {
        parent::__construct(sprintf('PHP version not valid for parser %s', $phpVersion));
    }
}
