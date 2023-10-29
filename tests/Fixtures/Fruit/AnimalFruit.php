<?php

declare(strict_types=1);

namespace Modulith\ArchCheck\Test\Fixtures\Fruit;

use Modulith\ArchCheck\Test\Fixtures\Animal\Cat;

final class AnimalFruit extends Banana
{
    /**
     * @var Cat
     */
    private $cat;

    public function __construct(Cat $cat)
    {
        $this->cat = $cat;
    }
}
