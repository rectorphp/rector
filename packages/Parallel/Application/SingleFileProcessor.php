<?php

declare (strict_types=1);
namespace Rector\Parallel\Application;

use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Symplify\SmartFileSystem\SmartFileInfo;
/**
 * @api @todo complete parallel run
 */
final class SingleFileProcessor
{
    /**
     * @return array<string, FileDiff[]>
     */
    public function processFileInfo(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo, \Rector\Core\ValueObject\Configuration $configuration) : array
    {
        // @todo implement
        return [];
    }
}
