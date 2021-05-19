<?php

declare (strict_types=1);
namespace RectorPrefix20210519\Symplify\PackageBuilder\Console\Command;

use RectorPrefix20210519\Symfony\Component\Console\Command\Command;
use RectorPrefix20210519\Symfony\Component\Console\Input\InputOption;
use RectorPrefix20210519\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix20210519\Symplify\PackageBuilder\ValueObject\Option;
use Symplify\SmartFileSystem\FileSystemGuard;
use Symplify\SmartFileSystem\Finder\SmartFinder;
use Symplify\SmartFileSystem\SmartFileSystem;
abstract class AbstractSymplifyCommand extends \RectorPrefix20210519\Symfony\Component\Console\Command\Command
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
        $this->addOption(\RectorPrefix20210519\Symplify\PackageBuilder\ValueObject\Option::CONFIG, 'c', \RectorPrefix20210519\Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Path to config file');
    }
    /**
     * @required
     */
    public function autowireAbstractSymplifyCommand(\RectorPrefix20210519\Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle, \Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem, \Symplify\SmartFileSystem\Finder\SmartFinder $smartFinder, \Symplify\SmartFileSystem\FileSystemGuard $fileSystemGuard) : void
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->smartFileSystem = $smartFileSystem;
        $this->smartFinder = $smartFinder;
        $this->fileSystemGuard = $fileSystemGuard;
    }
}
