<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider;

use PHPStan\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;
use PHPStan\Reflection\BetterReflection\SourceLocator\FileNodesFetcher;
use PHPStan\Reflection\BetterReflection\SourceLocator\OptimizedSingleFileSourceLocator;
use Rector\NodeTypeResolver\Contract\SourceLocatorProviderInterface;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DynamicSourceLocatorProvider implements SourceLocatorProviderInterface
{
    /**
     * @var SmartFileInfo[]
     */
    private $fileInfos = [];

    /**
     * @var FileNodesFetcher
     */
    private $fileNodesFetcher;

    public function __construct(FileNodesFetcher $fileNodesFetcher)
    {
        $this->fileNodesFetcher = $fileNodesFetcher;
    }

    public function setFileInfo(SmartFileInfo $fileInfo): void
    {
        $this->fileInfos = [$fileInfo];
    }

    /**
     * @param SmartFileInfo[] $fileInfos
     */
    public function addFileInfos(array $fileInfos): void
    {
        $this->fileInfos = array_merge($this->fileInfos, $fileInfos);
    }

    public function provide(): SourceLocator
    {
        $sourceLocators = [];
        foreach ($this->fileInfos as $fileInfo) {
            $sourceLocators[] = new OptimizedSingleFileSourceLocator($this->fileNodesFetcher, $fileInfo->getRealPath());
        }

        return new AggregateSourceLocator($sourceLocators);
    }
}
