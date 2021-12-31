<?php

declare (strict_types=1);
namespace Rector\Composer\Application\FileProcessor;

use Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory;
use Rector\Composer\Contract\Rector\ComposerRectorInterface;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\Error\SystemError;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Rector\Parallel\ValueObject\Bridge;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use RectorPrefix20211231\Symplify\ComposerJsonManipulator\ComposerJsonFactory;
use RectorPrefix20211231\Symplify\ComposerJsonManipulator\Printer\ComposerJsonPrinter;
use Symplify\SmartFileSystem\SmartFileInfo;
final class ComposerFileProcessor implements \Rector\Core\Contract\Processor\FileProcessorInterface
{
    /**
     * @readonly
     * @var \Symplify\ComposerJsonManipulator\ComposerJsonFactory
     */
    private $composerJsonFactory;
    /**
     * @readonly
     * @var \Symplify\ComposerJsonManipulator\Printer\ComposerJsonPrinter
     */
    private $composerJsonPrinter;
    /**
     * @readonly
     * @var \Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory
     */
    private $fileDiffFactory;
    /**
     * @var ComposerRectorInterface[]
     * @readonly
     */
    private $composerRectors;
    /**
     * @param ComposerRectorInterface[] $composerRectors
     */
    public function __construct(\RectorPrefix20211231\Symplify\ComposerJsonManipulator\ComposerJsonFactory $composerJsonFactory, \RectorPrefix20211231\Symplify\ComposerJsonManipulator\Printer\ComposerJsonPrinter $composerJsonPrinter, \Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory $fileDiffFactory, array $composerRectors)
    {
        $this->composerJsonFactory = $composerJsonFactory;
        $this->composerJsonPrinter = $composerJsonPrinter;
        $this->fileDiffFactory = $fileDiffFactory;
        $this->composerRectors = $composerRectors;
    }
    /**
     * @return array{system_errors: SystemError[], file_diffs: FileDiff[]}
     */
    public function process(\Rector\Core\ValueObject\Application\File $file, \Rector\Core\ValueObject\Configuration $configuration) : array
    {
        $systemErrorsAndFileDiffs = [\Rector\Parallel\ValueObject\Bridge::SYSTEM_ERRORS => [], \Rector\Parallel\ValueObject\Bridge::FILE_DIFFS => []];
        if ($this->composerRectors === []) {
            return $systemErrorsAndFileDiffs;
        }
        // to avoid modification of file
        $smartFileInfo = $file->getSmartFileInfo();
        $oldFileContents = $smartFileInfo->getContents();
        $composerJson = $this->composerJsonFactory->createFromFileInfo($smartFileInfo);
        $oldComposerJson = clone $composerJson;
        foreach ($this->composerRectors as $composerRector) {
            $composerRector->refactor($composerJson);
        }
        // nothing has changed
        if ($oldComposerJson->getJsonArray() === $composerJson->getJsonArray()) {
            return $systemErrorsAndFileDiffs;
        }
        $changedFileContent = $this->composerJsonPrinter->printToString($composerJson);
        $file->changeFileContent($changedFileContent);
        $fileDiff = $this->fileDiffFactory->createFileDiff($file, $oldFileContents, $changedFileContent);
        $systemErrorsAndFileDiffs[\Rector\Parallel\ValueObject\Bridge::FILE_DIFFS] = [$fileDiff];
        return $systemErrorsAndFileDiffs;
    }
    public function supports(\Rector\Core\ValueObject\Application\File $file, \Rector\Core\ValueObject\Configuration $configuration) : bool
    {
        $smartFileInfo = $file->getSmartFileInfo();
        if ($this->isJsonInTests($smartFileInfo)) {
            return \true;
        }
        return $smartFileInfo->getBasename() === 'composer.json';
    }
    /**
     * @return string[]
     */
    public function getSupportedFileExtensions() : array
    {
        return ['json'];
    }
    private function isJsonInTests(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo) : bool
    {
        if (!\Rector\Testing\PHPUnit\StaticPHPUnitEnvironment::isPHPUnitRun()) {
            return \false;
        }
        return $fileInfo->hasSuffixes(['json']);
    }
}
