<?php

declare (strict_types=1);
namespace Rector\ChangesReporting\ValueObjectFactory;

use Rector\Core\Differ\DefaultDiffer;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Reporting\FileDiff;
use RectorPrefix202208\Symplify\PackageBuilder\Console\Output\ConsoleDiffer;
use RectorPrefix202208\Symplify\SmartFileSystem\SmartFileSystem;
final class FileDiffFactory
{
    /**
     * @readonly
     * @var \Rector\Core\Differ\DefaultDiffer
     */
    private $defaultDiffer;
    /**
     * @readonly
     * @var \Symplify\PackageBuilder\Console\Output\ConsoleDiffer
     */
    private $consoleDiffer;
    /**
     * @readonly
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    private $smartFileSystem;
    public function __construct(DefaultDiffer $defaultDiffer, ConsoleDiffer $consoleDiffer, SmartFileSystem $smartFileSystem)
    {
        $this->defaultDiffer = $defaultDiffer;
        $this->consoleDiffer = $consoleDiffer;
        $this->smartFileSystem = $smartFileSystem;
    }
    public function createFileDiff(File $file, string $oldContent, string $newContent) : FileDiff
    {
        $relativeFilePath = $this->smartFileSystem->makePathRelative($file->getFilePath(), (string) \realpath(\getcwd()));
        $relativeFilePath = \rtrim($relativeFilePath, '/');
        // always keep the most recent diff
        return new FileDiff($relativeFilePath, $this->defaultDiffer->diff($oldContent, $newContent), $this->consoleDiffer->diff($oldContent, $newContent), $file->getRectorWithLineChanges());
    }
}
