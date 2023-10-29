<?php
declare(strict_types=1);

namespace Modulith\ArchCheck\Analyzer;

interface Parser
{
    public function parse(string $fileContent, string $filename): void;

    public function getClassDescriptions(): array;

    public function getParsingErrors(): array;
}
