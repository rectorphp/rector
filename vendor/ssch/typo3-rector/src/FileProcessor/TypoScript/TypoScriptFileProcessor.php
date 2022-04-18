<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript;

use RectorPrefix20220418\Helmich\TypoScriptParser\Parser\ParseError;
use RectorPrefix20220418\Helmich\TypoScriptParser\Parser\ParserInterface;
use RectorPrefix20220418\Helmich\TypoScriptParser\Parser\Printer\ASTPrinterInterface;
use RectorPrefix20220418\Helmich\TypoScriptParser\Parser\Printer\PrettyPrinterConfiguration;
use Helmich\TypoScriptParser\Parser\Traverser\Traverser;
use RectorPrefix20220418\Helmich\TypoScriptParser\Parser\Traverser\Visitor;
use RectorPrefix20220418\Helmich\TypoScriptParser\Tokenizer\TokenizerException;
use Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Console\Output\RectorOutputStyle;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\Error\SystemError;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Rector\FileFormatter\EditorConfig\EditorConfigParser;
use Rector\FileFormatter\ValueObject\Indent;
use Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Rector\Parallel\ValueObject\Bridge;
use Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\ConvertToPhpFileInterface;
use Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\TypoScriptPostRectorInterface;
use Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\TypoScriptRectorInterface;
use Ssch\TYPO3Rector\Contract\Processor\ConfigurableProcessorInterface;
use Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector\AbstractTypoScriptRector;
use RectorPrefix20220418\Symfony\Component\Console\Output\BufferedOutput;
use RectorPrefix20220418\Webmozart\Assert\Assert;
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
     * @var \Helmich\TypoScriptParser\Parser\ParserInterface
     */
    private $typoscriptParser;
    /**
     * @var \Symfony\Component\Console\Output\BufferedOutput
     */
    private $output;
    /**
     * @var \Helmich\TypoScriptParser\Parser\Printer\ASTPrinterInterface
     */
    private $typoscriptPrinter;
    /**
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    /**
     * @var \Rector\FileFormatter\EditorConfig\EditorConfigParser
     */
    private $editorConfigParser;
    /**
     * @var \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector
     */
    private $removedAndAddedFilesCollector;
    /**
     * @var \Rector\Core\Console\Output\RectorOutputStyle
     */
    private $rectorOutputStyle;
    /**
     * @var \Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory
     */
    private $fileDiffFactory;
    /**
     * @var TypoScriptRectorInterface[]
     */
    private $typoScriptRectors = [];
    /**
     * @var TypoScriptPostRectorInterface[]
     */
    private $typoScriptPostRectors = [];
    /**
     * @param TypoScriptRectorInterface[] $typoScriptRectors
     * @param TypoScriptPostRectorInterface[] $typoScriptPostRectors
     */
    public function __construct(\RectorPrefix20220418\Helmich\TypoScriptParser\Parser\ParserInterface $typoscriptParser, \RectorPrefix20220418\Symfony\Component\Console\Output\BufferedOutput $output, \RectorPrefix20220418\Helmich\TypoScriptParser\Parser\Printer\ASTPrinterInterface $typoscriptPrinter, \Rector\Core\Provider\CurrentFileProvider $currentFileProvider, \Rector\FileFormatter\EditorConfig\EditorConfigParser $editorConfigParser, \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector $removedAndAddedFilesCollector, \Rector\Core\Console\Output\RectorOutputStyle $rectorOutputStyle, \Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory $fileDiffFactory, array $typoScriptRectors = [], array $typoScriptPostRectors = [])
    {
        $this->typoscriptParser = $typoscriptParser;
        $this->output = $output;
        $this->typoscriptPrinter = $typoscriptPrinter;
        $this->currentFileProvider = $currentFileProvider;
        $this->editorConfigParser = $editorConfigParser;
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
        $this->rectorOutputStyle = $rectorOutputStyle;
        $this->fileDiffFactory = $fileDiffFactory;
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
        \RectorPrefix20220418\Webmozart\Assert\Assert::isArray($allowedFileExtensions);
        \RectorPrefix20220418\Webmozart\Assert\Assert::allString($allowedFileExtensions);
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
            $editorConfigConfigurationBuilder = \Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder::create();
            $editorConfigConfigurationBuilder->withIndent(\Rector\FileFormatter\ValueObject\Indent::createSpaceWithSize(4));
            $editorConfiguration = $this->editorConfigParser->extractConfigurationForFile($file, $editorConfigConfigurationBuilder);
            $prettyPrinterConfiguration = \RectorPrefix20220418\Helmich\TypoScriptParser\Parser\Printer\PrettyPrinterConfiguration::create();
            $prettyPrinterConfiguration = $prettyPrinterConfiguration->withEmptyLineBreaks();
            if ('tab' === $editorConfiguration->getIndentStyle()) {
                $prettyPrinterConfiguration = $prettyPrinterConfiguration->withTabs();
            } else {
                $prettyPrinterConfiguration = $prettyPrinterConfiguration->withSpaceIndentation($editorConfiguration->getIndentSize());
            }
            $prettyPrinterConfiguration = $prettyPrinterConfiguration->withClosingGlobalStatement();
            $this->typoscriptPrinter->setPrettyPrinterConfiguration($prettyPrinterConfiguration);
            $this->typoscriptPrinter->printStatements($originalStatements, $this->output);
            $newTypoScriptContent = $this->applyTypoScriptPostRectors($this->output->fetch());
            $typoScriptContent = \rtrim($newTypoScriptContent) . $editorConfiguration->getNewLine();
            $oldFileContents = $file->getFileContent();
            $file->changeFileContent($typoScriptContent);
            $this->fileDiffs[] = $this->fileDiffFactory->createFileDiff($file, $oldFileContents, $file->getFileContent());
        } catch (\RectorPrefix20220418\Helmich\TypoScriptParser\Tokenizer\TokenizerException $exception) {
            return;
        } catch (\RectorPrefix20220418\Helmich\TypoScriptParser\Parser\ParseError $exception) {
            $smartFileInfo = $file->getSmartFileInfo();
            $errorFile = $smartFileInfo->getRelativeFilePath();
            $this->rectorOutputStyle->warning(\sprintf('TypoScriptParser Error in: %s. File skipped.', $errorFile));
            return;
        }
    }
    /**
     * @return ConvertToPhpFileInterface[]
     */
    private function convertToPhpFileRectors() : array
    {
        return \array_filter($this->typoScriptRectors, function (\RectorPrefix20220418\Helmich\TypoScriptParser\Parser\Traverser\Visitor $visitor) : bool {
            return \is_a($visitor, \Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\ConvertToPhpFileInterface::class, \true);
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
}
