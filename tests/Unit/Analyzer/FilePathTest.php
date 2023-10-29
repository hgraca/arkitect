<?php

declare(strict_types=1);

namespace Modulith\ArchCheck\Test\Unit\Analyzer;

use Modulith\ArchCheck\Analyzer\FilePath;
use PHPUnit\Framework\TestCase;

class FilePathTest extends TestCase
{
    public function test_it_should_set_and_get_path(): void
    {
        $filePath = new FilePath();
        $filePath->set('thePath');

        $this->assertEquals('thePath', $filePath->toString());
    }
}
