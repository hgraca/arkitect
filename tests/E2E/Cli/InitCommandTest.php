<?php

declare(strict_types=1);

namespace Modulith\ArchCheck\Test\E2E\Cli;

use Modulith\ArchCheck\CLI\Application;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\ApplicationTester;

class InitCommandTest extends TestCase
{
    public function test_it_creates_a_file_in_default_dir(): void
    {
        $fs = vfsStream::setup()->url();

        $appTester = $this->runInit($fs);

        $output = $appTester->getDisplay();

        $this->assertFileExists($fs.'/archcheck.php');
        $this->assertStringContainsString('Creating archcheck.php file...', $output);
        $this->assertStringContainsString('customize it and run with php bin/archcheck check', $output);
    }

    public function test_it_creates_a_file_in_a_custom_dir(): void
    {
        $structure = [
            'nested' => [
                'path' => [],
            ],
        ];

        $fs = vfsStream::setup('root', null, $structure)->url();

        $appTester = $this->runInit($fs.'/nested/path');

        $output = $appTester->getDisplay();

        $this->assertFileExists($fs.'/nested/path/archcheck.php');
        $this->assertStringContainsString('Creating archcheck.php file...', $output);
        $this->assertStringContainsString('customize it and run with php bin/archcheck check', $output);
    }

    public function test_do_nothing_if_file_exists(): void
    {
        $structure = [
            'nested' => [
                'path' => [
                    'archcheck.php' => '',
                ],
            ],
        ];

        $fs = vfsStream::setup('root', null, $structure)->url();

        $appTester = $this->runInit($fs.'/nested/path');

        $this->assertStringContainsString(
            'File archcheck.php found in current directory, nothing to do',
            $appTester->getDisplay()
        );
    }

    public function test_returns_error_if_directory_is_not_writable(): void
    {
        $fs = vfsStream::setup('root', 0000)->url();

        $appTester = $this->runInit($fs);

        $this->assertStringContainsString(
            'Ops, it seems I cannot create the file in vfs://root',
            $appTester->getDisplay()
        );
    }

    private function runInit(string $path): ApplicationTester
    {
        $app = new Application();
        $app->setAutoExit(false);

        $appTester = new ApplicationTester($app);
        $appTester->run(['init', '--dest-dir' => $path]);

        return $appTester;
    }
}
