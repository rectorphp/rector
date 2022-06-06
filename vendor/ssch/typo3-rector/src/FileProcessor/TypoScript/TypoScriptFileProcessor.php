<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\FileProcessor\TypoScript;

use RectorPrefix20220606\Helmich\TypoScriptParser\Parser\AST\NestedAssignment;
use Helmich\TypoScriptParser\Parser\AST\Statement;
use RectorPrefix20220606\Helmich\TypoScriptParser\Parser\ParseError;
use RectorPrefix20220606\Helmich\TypoScriptParser\Parser\ParserInterface;
use RectorPrefix20220606\Helmich\TypoScriptParser\Parser\Printer\ASTPrinterInterface;
use RectorPrefix20220606\Helmich\TypoScriptParser\Parser\Printer\PrettyPrinterConfiguration;
use Helmich\TypoScriptParser\Parser\Traverser\Traverser;
use RectorPrefix20220606\Helmich\TypoScriptParser\Parser\Traverser\Visitor;
use RectorPrefix20220606\Helmich\TypoScriptParser\Tokenizer\TokenizerException;
use RectorPrefix20220606\Nette\Utils\Strings;
use RectorPrefix20220606\Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory;
use RectorPrefix20220606\Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use RectorPrefix20220606\Rector\Core\Console\Output\RectorOutputStyle;
use RectorPrefix20220606\Rector\Core\Provider\CurrentFileProvider;
use RectorPrefix20220606\Rector\Core\ValueObject\Application\File;
use RectorPrefix20220606\Rector\Core\ValueObject\Configuration;
use RectorPrefix20220606\Rector\Core\ValueObject\Error\SystemError;
use RectorPrefix20220606\Rector\Core\ValueObject\Reporting\FileDiff;
use RectorPrefix20220606\Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use RectorPrefix20220606\Rector\Parallel\ValueObject\Bridge;
use RectorPrefix20220606\Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\ConvertToPhpFileInterface;
use RectorPrefix20220606\Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\TypoScriptPostRectorInterface;
use RectorPrefix20220606\Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\TypoScriptRectorInterface;
use RectorPrefix20220606\Ssch\TYPO3Rector\Contract\Processor\ConfigurableProcessorInterface;
use RectorPrefix20220606\Ssch\TYPO3Rector\FileProcessor\TypoScript\Collector\RemoveTypoScriptStatementCollector;
use RectorPrefix20220606\Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector\AbstractTypoScriptRector;
use RectorPrefix20220606\Symfony\Component\Console\Output\BufferedOutput;
use RectorPrefix20220606\Webmozart\Assert\Assert;
/**
 * @see \Ssch\TYPO3Rector\Tests\FileProcessor\TypoScript\TypoScriptProcessorTest
 */
final class TypoScriptFileProcessor implements ConfigurableProcessorInterface
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
    public function __construct(ParserInterface $typoscriptParser, BufferedOutput $output, ASTPrinterInterface $typoscriptPrinter, CurrentFileProvider $currentFileProvider, RemovedAndAddedFilesCollector $removedAndAddedFilesCollector, RectorOutputStyle $rectorOutputStyle, FileDiffFactory $fileDiffFactory, RemoveTypoScriptStatementCollector $removeTypoScriptStatementCollector, array $typoScriptRectors = [], array $typoScriptPostRectors = [])
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
    public function supports(File $file, Configuration $configuration) : bool
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
    public function process(File $file, Configuration $configuration) : array
    {
        $this->processFile($file);
        $this->convertTypoScriptToPhpFiles();
        return [Bridge::SYSTEM_ERRORS => [], Bridge::FILE_DIFFS => $this->fileDiffs];
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
        Assert::isArray($allowedFileExtensions);
        Assert::allString($allowedFileExtensions);
        $this->allowedFileExtensions = $allowedFileExtensions;
    }
    private function processFile(File $file) : void
    {
        try {
            $this->currentFileProvider->setFile($file);
            $smartFileInfo = $file->getSmartFileInfo();
            $originalStatements = $this->typoscriptParser->parseString($smartFileInfo->getContents());
            $traverser = new Traverser($originalStatements);
            foreach ($this->typoScriptRectors as $visitor) {
                $traverser->addVisitor($visitor);
            }
            $traverser->walk();
            $typoscriptRectorsWithChange = \array_filter($this->typoScriptRectors, function (AbstractTypoScriptRector $typoScriptRector) {
                return $typoScriptRector->hasChanged();
            });
            if ([] === $typoscriptRectorsWithChange) {
                return;
            }
            // keep original json format
            $tabMatches = Strings::match($file->getFileContent(), "#^\n#");
            $indentStyle = $tabMatches ? 'tab' : 'space';
            $prettyPrinterConfiguration = PrettyPrinterConfiguration::create();
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
        } catch (TokenizerException $exception) {
            return;
        } catch (ParseError $exception) {
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
        return \array_filter($this->typoScriptRectors, function (Visitor $visitor) : bool {
            return $visitor instanceof ConvertToPhpFileInterface;
        });
    }
    private function convertTypoScriptToPhpFiles() : void
    {
        foreach ($this->convertToPhpFileRectors() as $convertToPhpFileVisitor) {
            $addedFileWithContent = $convertToPhpFileVisitor->convert();
            if (!$addedFileWithContent instanceof AddedFileWithContent) {
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
    private function filterRemovedStatements(array $originalStatements, File $file) : array
    {
        $printStatements = [];
        foreach ($originalStatements as $originalStatement) {
            if (!$this->removeTypoScriptStatementCollector->shouldStatementBeRemoved($originalStatement, $file)) {
                $printStatements[] = $originalStatement;
            }
            if ($originalStatement instanceof NestedAssignment) {
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
