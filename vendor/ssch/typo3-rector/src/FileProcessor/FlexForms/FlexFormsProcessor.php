<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\FlexForms;

use DOMDocument;
use Exception;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Ssch\TYPO3Rector\Contract\FileProcessor\FlexForms\Rector\FlexFormRectorInterface;
use UnexpectedValueException;
/**
 * @see \Ssch\TYPO3Rector\Tests\FileProcessor\FlexForms\FlexFormsProcessorTest
 */
final class FlexFormsProcessor implements \Rector\Core\Contract\Processor\FileProcessorInterface
{
    /**
     * @var \Ssch\TYPO3Rector\Contract\FileProcessor\FlexForms\Rector\FlexFormRectorInterface[]
     */
    private $flexFormRectors;
    /**
     * @param FlexFormRectorInterface[] $flexFormRectors
     */
    public function __construct(array $flexFormRectors)
    {
        $this->flexFormRectors = $flexFormRectors;
    }
    /**
     * @param \Rector\Core\ValueObject\Application\File $file
     * @param \Rector\Core\ValueObject\Configuration $configuration
     */
    public function process($file, $configuration) : void
    {
        if ([] === $this->flexFormRectors) {
            return;
        }
        $domDocument = new \DOMDocument();
        $domDocument->formatOutput = \true;
        $domDocument->loadXML($file->getFileContent());
        $hasChanged = \false;
        foreach ($this->flexFormRectors as $flexFormRector) {
            $hasChanged = $flexFormRector->transform($domDocument);
        }
        if (!$hasChanged) {
            return;
        }
        $xml = $domDocument->saveXML($domDocument->documentElement, \LIBXML_NOEMPTYTAG);
        if (\false === $xml) {
            throw new \UnexpectedValueException('Could not convert to xml');
        }
        if ($xml === $file->getFileContent()) {
            return;
        }
        $newFileContent = \html_entity_decode($xml);
        $file->changeFileContent($newFileContent);
    }
    /**
     * @param \Rector\Core\ValueObject\Application\File $file
     * @param \Rector\Core\ValueObject\Configuration $configuration
     */
    public function supports($file, $configuration) : bool
    {
        $smartFileInfo = $file->getSmartFileInfo();
        if (!\in_array($smartFileInfo->getExtension(), $this->getSupportedFileExtensions(), \true)) {
            return \false;
        }
        $fileContent = $file->getFileContent();
        try {
            $xml = @\simplexml_load_string($fileContent);
        } catch (\Exception $exception) {
            return \false;
        }
        if (\false === $xml) {
            return \false;
        }
        return 'T3DataStructure' === $xml->getName();
    }
    public function getSupportedFileExtensions() : array
    {
        return ['xml'];
    }
}
