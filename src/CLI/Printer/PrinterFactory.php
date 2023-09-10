<?php
declare(strict_types=1);

namespace Arkitect\CLI\Printer;

final class PrinterFactory
{
    public function create(?string $format = null): Printer
    {
        switch ($format) {
            case Printer::FORMAT_GITLAB:
                return new GitlabPrinter();
            case Printer::FORMAT_JSON:
                return new JsonPrinter();
            default:
                return new TextPrinter();
        }
    }
}
