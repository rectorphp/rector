<?php

declare (strict_types=1);
namespace RectorPrefix20210706\Symplify\PackageBuilder\Console\Command;

use RectorPrefix20210706\Symfony\Component\Console\Command\Command;
use RectorPrefix20210706\Symfony\Component\Console\Input\InputOption;
use RectorPrefix20210706\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix20210706\Symfony\Contracts\Service\Attribute\Required;
use RectorPrefix20210706\Symplify\PackageBuilder\ValueObject\Option;
use RectorPrefix20210706\Symplify\SmartFileSystem\FileSystemGuard;
use RectorPrefix20210706\Symplify\SmartFileSystem\Finder\SmartFinder;
use RectorPrefix20210706\Symplify\SmartFileSystem\SmartFileSystem;
abstract class AbstractSymplifyCommand extends \RectorPrefix20210706\Symfony\Component\Console\Command\Command
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
        $this->addOption(\RectorPrefix20210706\Symplify\PackageBuilder\ValueObject\Option::CONFIG, 'c', \RectorPrefix20210706\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Path to config file');
    }
    /**
     * @required
     * @param \Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle
     * @param \Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem
     * @param \Symplify\SmartFileSystem\Finder\SmartFinder $smartFinder
     * @param \Symplify\SmartFileSystem\FileSystemGuard $fileSystemGuard
     */
    public function autowireAbstractSymplifyCommand($symfonyStyle, $smartFileSystem, $smartFinder, $fileSystemGuard) : void
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->smartFileSystem = $smartFileSystem;
        $this->smartFinder = $smartFinder;
        $this->fileSystemGuard = $fileSystemGuard;
    }
}
