<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\PackageBuilder\Console\Command;

use RectorPrefix20210510\Symfony\Component\Console\Command\Command;
use RectorPrefix20210510\Symfony\Component\Console\Input\InputOption;
use RectorPrefix20210510\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix20210510\Symplify\PackageBuilder\ValueObject\Option;
use RectorPrefix20210510\Symplify\SmartFileSystem\FileSystemGuard;
use RectorPrefix20210510\Symplify\SmartFileSystem\Finder\SmartFinder;
use RectorPrefix20210510\Symplify\SmartFileSystem\SmartFileSystem;
abstract class AbstractSymplifyCommand extends Command
{
    /**
     * @var SymfonyStyle
     */
    protected $symfonyStyle;
    /**
     * @var SmartFileSystem
     */
    protected $smartFileSystem;
    /**
     * @var SmartFinder
     */
    protected $smartFinder;
    /**
     * @var FileSystemGuard
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
    public function autowireAbstractSymplifyCommand(SymfonyStyle $symfonyStyle, SmartFileSystem $smartFileSystem, SmartFinder $smartFinder, FileSystemGuard $fileSystemGuard) : void
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->smartFileSystem = $smartFileSystem;
        $this->smartFinder = $smartFinder;
        $this->fileSystemGuard = $fileSystemGuard;
    }
}
