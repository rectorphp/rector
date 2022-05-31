<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript;

use RectorPrefix20220531\Helmich\TypoScriptParser\Parser\AST\NestedAssignment;
use Helmich\TypoScriptParser\Parser\AST\Statement;
use RectorPrefix20220531\Helmich\TypoScriptParser\Parser\ParseError;
use RectorPrefix20220531\Helmich\TypoScriptParser\Parser\ParserInterface;
use RectorPrefix20220531\Helmich\TypoScriptParser\Parser\Printer\ASTPrinterInterface;
use RectorPrefix20220531\Helmich\TypoScriptParser\Parser\Printer\PrettyPrinterConfiguration;
use Helmich\TypoScriptParser\Parser\Traverser\Traverser;
use RectorPrefix20220531\Helmich\TypoScriptParser\Parser\Traverser\Visitor;
use RectorPrefix20220531\Helmich\TypoScriptParser\Tokenizer\TokenizerException;
use RectorPrefix20220531\Nette\Utils\Strings;
use Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Console\Output\RectorOutputStyle;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\Error\SystemError;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Rector\Parallel\ValueObject\Bridge;
use Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\ConvertToPhpFileInterface;
use Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\TypoScriptPostRectorInterface;
use Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\TypoScriptRectorInterface;
use Ssch\TYPO3Rector\Contract\Processor\ConfigurableProcessorInterface;
use Ssch\TYPO3Rector\FileProcessor\TypoScript\Collector\RemoveTypoScriptStatementCollector;
use Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector\AbstractTypoScriptRector;
use RectorPrefix20220531\Symfony\Component\Console\Output\BufferedOutput;
use RectorPrefix20220531\Webmozart\Assert\Assert;
/**
 * @see \Ssch\TYPO3Rector\Tests\FileProcessor\TypoScript\TypoScriptProcessorTest
 */
final class TypoScriptFileProcessor implements \Ssch\TYPO3Rector\Contract\Processor\ConfigurableProcessorInterface
{
    /**
     * @var string
     */
    public const ALLOWED_FILE_EXTENSIONS = 'allowed_file_extensions';
    /**
     * @var string[]
     */
    private $allowedFileExtensions = ['typoscript', 'ts', 'txt'];
    /**
     * @var FileDiff[]
     */
    private $fileDiffs = [];
    /**
     * @readonly
     * @var \Helmich\TypoScriptParser\Parser\ParserInterface
     */
    private $typoscriptParser;
    /**
     * @readonly
     * @var \Symfony\Component\Console\Output\BufferedOutput
     */
    private $output;
    /**
     * @readonly
     * @var \Helmich\TypoScriptParser\Parser\Printer\ASTPrinterInterface
     */
    private $typoscriptPrinter;
    /**
     * @readonly
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    /**
     * @readonly
     * @var \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector
     */
    private $removedAndAddedFilesCollector;
    /**
     * @readonly
     * @var \Rector\Core\Console\Output\RectorOutputStyle
     */
    private $rectorOutputStyle;
    /**
     * @readonly
     * @var \Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory
     */
    private $fileDiffFactory;
    /**
     * @readonly
     * @var \Ssch\TYPO3Rector\FileProcessor\TypoScript\Collector\RemoveTypoScriptStatementCollector
     */
    private $removeTypoScriptStatementCollector;
    /**
     * @var TypoScriptRectorInterface[]
     * @readonly
     */
    private $typoScriptRectors = [];
    /**
     * @var TypoScriptPostRectorInterface[]
     * @readonly
     */
    private $typoScriptPostRectors = [];
    /**
     * @param TypoScriptRectorInterface[] $typoScriptRectors
     * @param TypoScriptPostRectorInterface[] $typoScriptPostRectors
     */
    public function __construct(\RectorPrefix20220531\Helmich\TypoScriptParser\Parser\ParserInterface $typoscriptParser, \RectorPrefix20220531\Symfony\Component\Console\Output\BufferedOutput $output, \RectorPrefix20220531\Helmich\TypoScriptParser\Parser\Printer\ASTPrinterInterface $typoscriptPrinter, \Rector\Core\Provider\CurrentFileProvider $currentFileProvider, \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector $removedAndAddedFilesCollector, \Rector\Core\Console\Output\RectorOutputStyle $rectorOutputStyle, \Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory $fileDiffFactory, \Ssch\TYPO3Rector\FileProcessor\TypoScript\Collector\RemoveTypoScriptStatementCollector $removeTypoScriptStatementCollector, array $typoScriptRectors = [], array $typoScriptPostRectors = [])
    {
        $this->typoscriptParser = $typoscriptParser;
        $this->output = $output;
        $this->typoscriptPrinter = $typoscriptPrinter;
        $this->currentFileProvider = $currentFileProvider;
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
        $this->rectorOutputStyle = $rectorOutputStyle;
        $this->fileDiffFactory = $fileDiffFactory;
        $this->removeTypoScriptStatementCollector = $removeTypoScriptStatementCollector;
        $this->typoScriptRectors = $typoScriptRectors;
        $this->typoScriptPostRectors = $typoScriptPostRectors;
    }
    public function supports(\Rector\Core\ValueObject\Application\File $file, \Rector\Core\ValueObject\Configuration $configuration) : bool
    {
        if ([] === $this->typoScriptRectors) {
            return \false;
        }
        $smartFileInfo = $file->getSmartFileInfo();
        return \in_array($smartFileInfo->getExtension(), $this->allowedFileExtensions, \true);
    }
    /**
     * @return array{system_errors: SystemError[], file_diffs: FileDiff[]}
     */
    public function process(\Rector\Core\ValueObject\Application\File $file, \Rector\Core\ValueObject\Configuration $configuration) : array
    {
        $this->processFile($file);
        $this->convertTypoScriptToPhpFiles();
        return [\Rector\Parallel\ValueObject\Bridge::SYSTEM_ERRORS => [], \Rector\Parallel\ValueObject\Bridge::FILE_DIFFS => $this->fileDiffs];
    }
    /**
     * @return string[]
     */
    public function getSupportedFileExtensions() : array
    {
        return $this->allowedFileExtensions;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $allowedFileExtensions = $configuration[self::ALLOWED_FILE_EXTENSIONS] ?? $configuration;
        \RectorPrefix20220531\Webmozart\Assert\Assert::isArray($allowedFileExtensions);
        \RectorPrefix20220531\Webmozart\Assert\Assert::allString($allowedFileExtensions);
        $this->allowedFileExtensions = $allowedFileExtensions;
    }
    private function processFile(\Rector\Core\ValueObject\Application\File $file) : void
    {
        try {
            $this->currentFileProvider->setFile($file);
            $smartFileInfo = $file->getSmartFileInfo();
            $originalStatements = $this->typoscriptParser->parseString($smartFileInfo->getContents());
            $traverser = new \Helmich\TypoScriptParser\Parser\Traverser\Traverser($originalStatements);
            foreach ($this->typoScriptRectors as $visitor) {
                $traverser->addVisitor($visitor);
            }
            $traverser->walk();
            $typoscriptRectorsWithChange = \array_filter($this->typoScriptRectors, function (\Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector\AbstractTypoScriptRector $typoScriptRector) {
                return $typoScriptRector->hasChanged();
            });
            if ([] === $typoscriptRectorsWithChange) {
                return;
            }
            // keep original json format
            $tabMatches = \RectorPrefix20220531\Nette\Utils\Strings::match($file->getFileContent(), "#^\n#");
            $indentStyle = $tabMatches ? 'tab' : 'space';
            $prettyPrinterConfiguration = \RectorPrefix20220531\Helmich\TypoScriptParser\Parser\Printer\PrettyPrinterConfiguration::create();
            $prettyPrinterConfiguration = $prettyPrinterConfiguration->withEmptyLineBreaks();
            if ('tab' === $indentStyle) {
                $prettyPrinterConfiguration = $prettyPrinterConfiguration->withTabs();
            } else {
                // default indent
                $prettyPrinterConfiguration = $prettyPrinterConfiguration->withSpaceIndentation(4);
            }
            $prettyPrinterConfiguration = $prettyPrinterConfiguration->withClosingGlobalStatement();
            $this->typoscriptPrinter->setPrettyPrinterConfiguration($prettyPrinterConfiguration);
            $printStatements = $this->filterRemovedStatements($originalStatements, $file);
            $this->typoscriptPrinter->printStatements($printStatements, $this->output);
            $newTypoScriptContent = $this->applyTypoScriptPostRectors($this->output->fetch());
            $typoScriptContent = \rtrim($newTypoScriptContent) . "\n";
            $oldFileContents = $file->getFileContent();
            $file->changeFileContent($typoScriptContent);
            $this->fileDiffs[] = $this->fileDiffFactory->createFileDiff($file, $oldFileContents, $file->getFileContent());
        } catch (\RectorPrefix20220531\Helmich\TypoScriptParser\Tokenizer\TokenizerException $exception) {
            return;
        } catch (\RectorPrefix20220531\Helmich\TypoScriptParser\Parser\ParseError $exception) {
            $smartFileInfo = $file->getSmartFileInfo();
            $errorFile = $smartFileInfo->getRelativeFilePath();
            $this->rectorOutputStyle->warning(\sprintf('TypoScriptParser Error in: %s. File skipped.', $errorFile));
        }
    }
    /**
     * @return ConvertToPhpFileInterface[]
     */
    private function convertToPhpFileRectors() : array
    {
        return \array_filter($this->typoScriptRectors, function (\RectorPrefix20220531\Helmich\TypoScriptParser\Parser\Traverser\Visitor $visitor) : bool {
            return $visitor instanceof \Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\ConvertToPhpFileInterface;
        });
    }
    private function convertTypoScriptToPhpFiles() : void
    {
        foreach ($this->convertToPhpFileRectors() as $convertToPhpFileVisitor) {
            $addedFileWithContent = $convertToPhpFileVisitor->convert();
            if (!$addedFileWithContent instanceof \Rector\FileSystemRector\ValueObject\AddedFileWithContent) {
                continue;
            }
            $this->removedAndAddedFilesCollector->addAddedFile($addedFileWithContent);
            $this->rectorOutputStyle->warning($convertToPhpFileVisitor->getMessage());
        }
    }
    private function applyTypoScriptPostRectors(string $content) : string
    {
        foreach ($this->typoScriptPostRectors as $typoScriptPostRector) {
            $content = $typoScriptPostRector->apply($content);
        }
        return $content;
    }
    /**
     * @param Statement[] $originalStatements
     *
     * @return Statement[]
     */
    private function filterRemovedStatements(array $originalStatements, \Rector\Core\ValueObject\Application\File $file) : array
    {
        $printStatements = [];
        foreach ($originalStatements as $originalStatement) {
            if (!$this->removeTypoScriptStatementCollector->shouldStatementBeRemoved($originalStatement, $file)) {
                $printStatements[] = $originalStatement;
            }
            if ($originalStatement instanceof \RectorPrefix20220531\Helmich\TypoScriptParser\Parser\AST\NestedAssignment) {
                $originalNestedStatements = [];
                foreach ($originalStatement->statements as $nestedOriginalStatement) {
                    if (!$this->removeTypoScriptStatementCollector->shouldStatementBeRemoved($nestedOriginalStatement, $file)) {
                        $originalNestedStatements[] = $nestedOriginalStatement;
                    }
                }
                $originalStatement->statements = $originalNestedStatements;
            }
        }
        return $printStatements;
    }
}
