<?php

declare (strict_types=1);
namespace Rector\Core\Logging;

use PHPStan\Internal\BytesHelper;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\FileSystem\FilePathHelper;
use RectorPrefix202308\Symfony\Component\Console\Style\SymfonyStyle;
final class RectorOutput
{
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    /**
     * @readonly
     * @var \Rector\Core\FileSystem\FilePathHelper
     */
    private $filePathHelper;
    /**
     * @var float|null
     */
    private $startTime;
    /**
     * @var int|null
     */
    private $previousMemory;
    public function __construct(SymfonyStyle $symfonyStyle, FilePathHelper $filePathHelper)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->filePathHelper = $filePathHelper;
    }
    public function isDebug() : bool
    {
        return $this->symfonyStyle->isDebug();
    }
    /**
     * @param class-string<RectorInterface> $rectorClass
     */
    public function printCurrentFileAndRule(string $filePath, string $rectorClass) : void
    {
        $relativeFilePath = $this->filePathHelper->relativePath($filePath);
        $this->symfonyStyle->writeln('[file] ' . $relativeFilePath);
        $this->symfonyStyle->writeln('[rule] ' . $rectorClass);
    }
    public function startConsumptions() : void
    {
        $this->startTime = \microtime(\true);
        $this->previousMemory = \memory_get_peak_usage(\true);
    }
    public function printConsumptions() : void
    {
        if ($this->startTime === null || $this->previousMemory === null) {
            return;
        }
        $elapsedTime = \microtime(\true) - $this->startTime;
        $currentTotalMemory = \memory_get_peak_usage(\true);
        $previousMemory = $this->previousMemory;
        $consumed = \sprintf('--- consumed %s, total %s, took %.2f s', BytesHelper::bytes($currentTotalMemory - $previousMemory), BytesHelper::bytes($currentTotalMemory), $elapsedTime);
        $this->symfonyStyle->writeln($consumed);
        $this->symfonyStyle->newLine(1);
    }
}
