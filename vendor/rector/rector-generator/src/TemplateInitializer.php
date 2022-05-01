<?php

declare (strict_types=1);
namespace Rector\RectorGenerator;

use RectorPrefix20220501\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix20220501\Symplify\SmartFileSystem\FileSystemGuard;
use RectorPrefix20220501\Symplify\SmartFileSystem\SmartFileSystem;
final class TemplateInitializer
{
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    /**
     * @readonly
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    private $smartFileSystem;
    /**
     * @readonly
     * @var \Symplify\SmartFileSystem\FileSystemGuard
     */
    private $fileSystemGuard;
    public function __construct(\RectorPrefix20220501\Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle, \RectorPrefix20220501\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem, \RectorPrefix20220501\Symplify\SmartFileSystem\FileSystemGuard $fileSystemGuard)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->smartFileSystem = $smartFileSystem;
        $this->fileSystemGuard = $fileSystemGuard;
    }
    public function initialize(string $templateFilePath, string $rootFileName) : void
    {
        $this->fileSystemGuard->ensureFileExists($templateFilePath, __METHOD__);
        $targetFilePath = \getcwd() . '/' . $rootFileName;
        $doesFileExist = $this->smartFileSystem->exists($targetFilePath);
        if ($doesFileExist) {
            $message = \sprintf('Config file "%s" already exists', $rootFileName);
            $this->symfonyStyle->warning($message);
        } else {
            $this->smartFileSystem->copy($templateFilePath, $targetFilePath);
            $message = \sprintf('"%s" config file was added', $rootFileName);
            $this->symfonyStyle->success($message);
        }
    }
}
