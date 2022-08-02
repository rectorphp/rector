<?php

declare (strict_types=1);
namespace RectorPrefix202208\Symplify\PackageBuilder\Console\Command;

use RectorPrefix202208\Symfony\Component\Console\Command\Command;
use RectorPrefix202208\Symfony\Component\Console\Input\InputOption;
use RectorPrefix202208\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix202208\Symfony\Contracts\Service\Attribute\Required;
use RectorPrefix202208\Symplify\PackageBuilder\ValueObject\Option;
use RectorPrefix202208\Symplify\SmartFileSystem\FileSystemGuard;
use RectorPrefix202208\Symplify\SmartFileSystem\Finder\SmartFinder;
use RectorPrefix202208\Symplify\SmartFileSystem\SmartFileSystem;
abstract class AbstractSymplifyCommand extends Command
{
    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    protected $symfonyStyle;
    /**
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    protected $smartFileSystem;
    /**
     * @var \Symplify\SmartFileSystem\Finder\SmartFinder
     */
    protected $smartFinder;
    /**
     * @var \Symplify\SmartFileSystem\FileSystemGuard
     */
    protected $fileSystemGuard;
    public function __construct()
    {
        parent::__construct();
        $this->addOption(Option::CONFIG, 'c', InputOption::VALUE_REQUIRED, 'Path to config file');
    }
    /**
     * @required
     */
    public function autowire(SymfonyStyle $symfonyStyle, SmartFileSystem $smartFileSystem, SmartFinder $smartFinder, FileSystemGuard $fileSystemGuard) : void
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->smartFileSystem = $smartFileSystem;
        $this->smartFinder = $smartFinder;
        $this->fileSystemGuard = $fileSystemGuard;
    }
}
