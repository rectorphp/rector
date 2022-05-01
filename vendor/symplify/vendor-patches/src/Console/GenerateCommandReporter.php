<?php

declare (strict_types=1);
namespace RectorPrefix20220501\Symplify\VendorPatches\Console;

use RectorPrefix20220501\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix20220501\Symplify\VendorPatches\ValueObject\OldAndNewFileInfo;
final class GenerateCommandReporter
{
    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    public function __construct(\RectorPrefix20220501\Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }
    public function reportIdenticalNewAndOldFile(\RectorPrefix20220501\Symplify\VendorPatches\ValueObject\OldAndNewFileInfo $oldAndNewFileInfo) : void
    {
        $message = \sprintf('Files "%s" and "%s" have the same content. Did you forgot to change it?', $oldAndNewFileInfo->getOldFileRelativePath(), $oldAndNewFileInfo->getNewFileRelativePath());
        $this->symfonyStyle->warning($message);
    }
}
