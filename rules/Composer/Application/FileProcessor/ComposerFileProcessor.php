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
use RectorPrefix202208\Symplify\ComposerJsonManipulator\ComposerJsonFactory;
use RectorPrefix202208\Symplify\ComposerJsonManipulator\Printer\ComposerJsonPrinter;
use RectorPrefix202208\Symplify\SmartFileSystem\SmartFileInfo;
final class ComposerFileProcessor implements FileProcessorInterface
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
    public function __construct(ComposerJsonFactory $composerJsonFactory, ComposerJsonPrinter $composerJsonPrinter, FileDiffFactory $fileDiffFactory, array $composerRectors)
    {
        $this->composerJsonFactory = $composerJsonFactory;
        $this->composerJsonPrinter = $composerJsonPrinter;
        $this->fileDiffFactory = $fileDiffFactory;
        $this->composerRectors = $composerRectors;
    }
    /**
     * @return array{system_errors: SystemError[], file_diffs: FileDiff[]}
     */
    public function process(File $file, Configuration $configuration) : array
    {
        $systemErrorsAndFileDiffs = [Bridge::SYSTEM_ERRORS => [], Bridge::FILE_DIFFS => []];
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
        $systemErrorsAndFileDiffs[Bridge::FILE_DIFFS] = [$fileDiff];
        return $systemErrorsAndFileDiffs;
    }
    public function supports(File $file, Configuration $configuration) : bool
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
    private function isJsonInTests(SmartFileInfo $fileInfo) : bool
    {
        if (!StaticPHPUnitEnvironment::isPHPUnitRun()) {
            return \false;
        }
        return $fileInfo->hasSuffixes(['json']);
    }
}
