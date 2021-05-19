<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Helper;

use RectorPrefix20210519\Nette\Utils\Strings;
use Symplify\SmartFileSystem\SmartFileInfo;
trait FileHelperTrait
{
    private function isExtLocalConf(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo) : bool
    {
        return \RectorPrefix20210519\Nette\Utils\Strings::endsWith($fileInfo->getFilename(), 'ext_localconf.php');
    }
    private function isExtTables(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo) : bool
    {
        return \RectorPrefix20210519\Nette\Utils\Strings::endsWith($fileInfo->getFilename(), 'ext_tables.php');
    }
    private function isExtEmconf(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo) : bool
    {
        return \RectorPrefix20210519\Nette\Utils\Strings::endsWith($fileInfo->getFilename(), 'ext_emconf.php');
    }
}
