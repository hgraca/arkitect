<?php
declare(strict_types=1);

namespace Modulith\ArchCheck\CLI;

use Modulith\ArchCheck\CLI\Command\Check;
use Modulith\ArchCheck\CLI\Command\DebugExpression;
use Modulith\ArchCheck\CLI\Command\Init;

class Application extends \Symfony\Component\Console\Application
{
    /** @var string */
    private static $logo = <<< 'EOD'
  ____  _   _ ____   _         _    _ _            _
 |  _ \| | | |  _ \ / \   _ __| | _(_) |_ ___  ___| |_
 | |_) | |_| | |_) / _ \ | '__| |/ / | __/ _ \/ __| __|
 |  __/|  _  |  __/ ___ \| |  |   <| | ||  __/ (__| |_
 |_|   |_| |_|_| /_/   \_\_|  |_|\_\_|\__\___|\___|\__|
EOD;

    public function __construct()
    {
        parent::__construct('Archcheck', Version::get());
        $this->add(new Check());
        $this->add(new Init());
        $this->add(new DebugExpression());
    }

    public function getLongVersion()
    {
        return sprintf("%s\n\n<info>%s</info> version <comment>%s</comment>", self::$logo, $this->getName(), $this->getVersion());
    }
}
