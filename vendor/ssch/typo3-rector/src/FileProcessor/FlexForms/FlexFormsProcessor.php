<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\FileProcessor\FlexForms;

use DOMDocument;
use Exception;
use RectorPrefix20220606\Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory;
use RectorPrefix20220606\Rector\Core\Contract\Processor\FileProcessorInterface;
use RectorPrefix20220606\Rector\Core\ValueObject\Application\File;
use RectorPrefix20220606\Rector\Core\ValueObject\Configuration;
use RectorPrefix20220606\Rector\Core\ValueObject\Error\SystemError;
use RectorPrefix20220606\Rector\Core\ValueObject\Reporting\FileDiff;
use RectorPrefix20220606\Rector\Parallel\ValueObject\Bridge;
use RectorPrefix20220606\Ssch\TYPO3Rector\Contract\FileProcessor\FlexForms\Rector\FlexFormRectorInterface;
use UnexpectedValueException;
/**
 * @see \Ssch\TYPO3Rector\Tests\FileProcessor\FlexForms\FlexFormsProcessorTest
 */
final class FlexFormsProcessor implements FileProcessorInterface
{
    /**
     * @var FlexFormRectorInterface[]
     * @readonly
     */
    private $flexFormRectors;
    /**
     * @readonly
     * @var \Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory
     */
    private $fileDiffFactory;
    /**
     * @param FlexFormRectorInterface[] $flexFormRectors
     */
    public function __construct(array $flexFormRectors, FileDiffFactory $fileDiffFactory)
    {
        $this->flexFormRectors = $flexFormRectors;
        $this->fileDiffFactory = $fileDiffFactory;
    }
    /**
     * @return array{system_errors: SystemError[], file_diffs: FileDiff[]}
     */
    public function process(File $file, Configuration $configuration) : array
    {
        $systemErrorsAndFileDiffs = [Bridge::SYSTEM_ERRORS => [], Bridge::FILE_DIFFS => []];
        $oldFileContents = $file->getFileContent();
        $domDocument = new DOMDocument();
        $domDocument->formatOutput = \true;
        $domDocument->loadXML($oldFileContents);
        $hasChanged = \false;
        foreach ($this->flexFormRectors as $flexFormRector) {
            $hasChanged = $flexFormRector->transform($domDocument);
        }
        if (!$hasChanged) {
            return $systemErrorsAndFileDiffs;
        }
        $xml = $domDocument->saveXML($domDocument->documentElement, \LIBXML_NOEMPTYTAG);
        if (\false === $xml) {
            throw new UnexpectedValueException('Could not convert to xml');
        }
        // add end of line
        $xml .= \PHP_EOL;
        // nothing has changed
        if ($oldFileContents === $xml) {
            return $systemErrorsAndFileDiffs;
        }
        $newFileContent = \html_entity_decode($xml);
        $file->changeFileContent($newFileContent);
        $fileDiff = $this->fileDiffFactory->createFileDiff($file, $oldFileContents, $newFileContent);
        $systemErrorsAndFileDiffs[Bridge::FILE_DIFFS][] = $fileDiff;
        return $systemErrorsAndFileDiffs;
    }
    public function supports(File $file, Configuration $configuration) : bool
    {
        // avoid empty run
        if ([] === $this->flexFormRectors) {
            return \false;
        }
        $smartFileInfo = $file->getSmartFileInfo();
        if (!\in_array($smartFileInfo->getExtension(), $this->getSupportedFileExtensions(), \true)) {
            return \false;
        }
        $fileContent = $file->getFileContent();
        try {
            $xml = @\simplexml_load_string($fileContent);
        } catch (Exception $exception) {
            return \false;
        }
        if (\false === $xml) {
            return \false;
        }
        return 'T3DataStructure' === $xml->getName();
    }
    /**
     * @return string[]
     */
    public function getSupportedFileExtensions() : array
    {
        return ['xml'];
    }
}
