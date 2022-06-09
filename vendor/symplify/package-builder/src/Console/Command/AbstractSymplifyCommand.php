<?php

declare (strict_types=1);
namespace RectorPrefix20220609\Symplify\PackageBuilder\Console\Command;

use RectorPrefix20220609\Symfony\Component\Console\Command\Command;
use RectorPrefix20220609\Symfony\Component\Console\Input\InputOption;
use RectorPrefix20220609\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix20220609\Symfony\Contracts\Service\Attribute\Required;
use RectorPrefix20220609\Symplify\PackageBuilder\ValueObject\Option;
use RectorPrefix20220609\Symplify\SmartFileSystem\FileSystemGuard;
use RectorPrefix20220609\Symplify\SmartFileSystem\Finder\SmartFinder;
use RectorPrefix20220609\Symplify\SmartFileSystem\SmartFileSystem;
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
