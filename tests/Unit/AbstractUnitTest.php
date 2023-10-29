<?php

declare(strict_types=1);

namespace Modulith\ArchCheck\Test\Unit;

use Modulith\ArchCheck\Analyzer\ClassDescription;
use Modulith\ArchCheck\Analyzer\FileParserFactory;
use Modulith\ArchCheck\Exceptions\ClassFileNotFoundException;
use PHPUnit\Framework\TestCase;

abstract class AbstractUnitTest extends TestCase
{
    /**
     * @param class-string $fqcn
     *
     * @throws ClassFileNotFoundException
     * @throws \ReflectionException
     */
    public function getClassDescription(string $fqcn): ClassDescription
    {
        $reflector = new \ReflectionClass($fqcn);
        $filename = $reflector->getFileName();
        if (false === $filename) {
            throw new ClassFileNotFoundException($fqcn);
        }

        $fileParser = FileParserFactory::createFileParser();
        $fileParser->parse(file_get_contents($filename), $filename);
        $classDescriptionList = $fileParser->getClassDescriptions();

        return array_pop($classDescriptionList);
    }
}
