<?php

declare(strict_types=1);

namespace Modulith\ArchCheck\Test\Fixtures\ComponentB;

use Modulith\ArchCheck\Test\Fixtures\ComponentA\ClassAWithoutDependencies;
use Modulith\ArchCheck\Test\Fixtures\ComponentC\ComponentCA\ClassCAWithoutDependencies;

final class ClassBDependingOnAD
{
    private $a;

    private $d;

    public function __construct()
    {
        $this->a = new ClassAWithoutDependencies();
        $this->d = new ClassCAWithoutDependencies();
    }
}
