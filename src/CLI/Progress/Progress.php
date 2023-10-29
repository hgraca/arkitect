<?php
declare(strict_types=1);

namespace Modulith\ArchCheck\CLI\Progress;

use Modulith\ArchCheck\ClassSet;

interface Progress
{
    public function startFileSetAnalysis(ClassSet $set): void;

    public function startParsingFile(string $file): void;

    public function endParsingFile(string $file): void;

    public function endFileSetAnalysis(ClassSet $set): void;
}
