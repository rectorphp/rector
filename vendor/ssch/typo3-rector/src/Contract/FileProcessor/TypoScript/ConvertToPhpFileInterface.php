<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript;

use RectorPrefix20220606\Rector\Core\Contract\Rector\RectorInterface;
use RectorPrefix20220606\Rector\FileSystemRector\ValueObject\AddedFileWithContent;
interface ConvertToPhpFileInterface extends RectorInterface
{
    public function convert() : ?AddedFileWithContent;
    public function getMessage() : string;
}
