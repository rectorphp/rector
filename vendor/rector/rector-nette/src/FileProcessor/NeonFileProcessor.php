<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Nette\FileProcessor;

use RectorPrefix20220606\Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory;
use RectorPrefix20220606\Rector\Core\Contract\Processor\FileProcessorInterface;
use RectorPrefix20220606\Rector\Core\ValueObject\Application\File;
use RectorPrefix20220606\Rector\Core\ValueObject\Configuration;
use RectorPrefix20220606\Rector\Core\ValueObject\Error\SystemError;
use RectorPrefix20220606\Rector\Core\ValueObject\Reporting\FileDiff;
use RectorPrefix20220606\Rector\Nette\Contract\Rector\NeonRectorInterface;
use RectorPrefix20220606\Rector\Nette\NeonParser\NeonNodeTraverserFactory;
use RectorPrefix20220606\Rector\Nette\NeonParser\NeonParser;
use RectorPrefix20220606\Rector\Nette\NeonParser\Printer\FormatPreservingNeonPrinter;
use RectorPrefix20220606\Rector\Parallel\ValueObject\Bridge;
final class NeonFileProcessor implements FileProcessorInterface
{
    /**
     * @readonly
     * @var \Rector\Nette\NeonParser\NeonParser
     */
    private $neonParser;
    /**
     * @readonly
     * @var \Rector\Nette\NeonParser\NeonNodeTraverserFactory
     */
    private $neonNodeTraverserFactory;
    /**
     * @readonly
     * @var \Rector\Nette\NeonParser\Printer\FormatPreservingNeonPrinter
     */
    private $formatPreservingNeonPrinter;
    /**
     * @var NeonRectorInterface[]
     * @readonly
     */
    private $neonRectors;
    /**
     * @readonly
     * @var \Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory
     */
    private $fileDiffFactory;
    /**
     * @param NeonRectorInterface[] $neonRectors
     */
    public function __construct(NeonParser $neonParser, NeonNodeTraverserFactory $neonNodeTraverserFactory, FormatPreservingNeonPrinter $formatPreservingNeonPrinter, array $neonRectors, FileDiffFactory $fileDiffFactory)
    {
        $this->neonParser = $neonParser;
        $this->neonNodeTraverserFactory = $neonNodeTraverserFactory;
        $this->formatPreservingNeonPrinter = $formatPreservingNeonPrinter;
        $this->neonRectors = $neonRectors;
        $this->fileDiffFactory = $fileDiffFactory;
    }
    /**
     * @return array{system_errors: SystemError[], file_diffs: FileDiff[]}
     */
    public function process(File $file, Configuration $configuration) : array
    {
        $systemErrorsAndFileDiffs = [Bridge::SYSTEM_ERRORS => [], Bridge::FILE_DIFFS => []];
        if ($this->neonRectors === []) {
            return $systemErrorsAndFileDiffs;
        }
        $fileContent = $file->getFileContent();
        $neonNode = $this->neonParser->parseString($fileContent);
        $neonNodeTraverser = $this->neonNodeTraverserFactory->create();
        foreach ($this->neonRectors as $neonRector) {
            $neonNodeTraverser->addNeonNodeVisitor($neonRector);
        }
        $originalPrintedContent = $this->formatPreservingNeonPrinter->printNode($neonNode, $fileContent);
        $neonNode = $neonNodeTraverser->traverse($neonNode);
        $changedFileContent = $this->formatPreservingNeonPrinter->printNode($neonNode, $fileContent);
        // has node changed?
        if ($changedFileContent === $originalPrintedContent) {
            return $systemErrorsAndFileDiffs;
        }
        $file->changeFileContent($changedFileContent);
        $fileDiff = $this->fileDiffFactory->createFileDiff($file, $originalPrintedContent, $changedFileContent);
        $systemErrorsAndFileDiffs[Bridge::FILE_DIFFS][] = $fileDiff;
        return $systemErrorsAndFileDiffs;
    }
    public function supports(File $file, Configuration $configuration) : bool
    {
        $fileInfo = $file->getSmartFileInfo();
        return $fileInfo->hasSuffixes($this->getSupportedFileExtensions());
    }
    /**
     * @return string[]
     */
    public function getSupportedFileExtensions() : array
    {
        return ['neon'];
    }
}
