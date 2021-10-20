<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript;

use RectorPrefix20211020\Helmich\TypoScriptParser\Parser\ParseError;
use RectorPrefix20211020\Helmich\TypoScriptParser\Parser\ParserInterface;
use RectorPrefix20211020\Helmich\TypoScriptParser\Parser\Printer\ASTPrinterInterface;
use RectorPrefix20211020\Helmich\TypoScriptParser\Parser\Printer\PrettyPrinterConfiguration;
use Helmich\TypoScriptParser\Parser\Traverser\Traverser;
use RectorPrefix20211020\Helmich\TypoScriptParser\Parser\Traverser\Visitor;
use RectorPrefix20211020\Helmich\TypoScriptParser\Tokenizer\TokenizerException;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Console\Output\RectorOutputStyle;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\FileFormatter\EditorConfig\EditorConfigParser;
use Rector\FileFormatter\ValueObject\Indent;
use Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\ConvertToPhpFileInterface;
use Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\TypoScriptRectorInterface;
use Ssch\TYPO3Rector\Contract\Processor\ConfigurableProcessorInterface;
use Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector\AbstractTypoScriptRector;
use RectorPrefix20211020\Symfony\Component\Console\Output\BufferedOutput;
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
     * @var \Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\TypoScriptRectorInterface[]
     */
    private $typoScriptRectors = [];
    /**
     * @param TypoScriptRectorInterface[] $typoScriptRectors
     */
    public function __construct(\RectorPrefix20211020\Helmich\TypoScriptParser\Parser\ParserInterface $typoscriptParser, \RectorPrefix20211020\Symfony\Component\Console\Output\BufferedOutput $output, \RectorPrefix20211020\Helmich\TypoScriptParser\Parser\Printer\ASTPrinterInterface $typoscriptPrinter, \Rector\Core\Provider\CurrentFileProvider $currentFileProvider, \Rector\FileFormatter\EditorConfig\EditorConfigParser $editorConfigParser, \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector $removedAndAddedFilesCollector, \Rector\Core\Console\Output\RectorOutputStyle $rectorOutputStyle, array $typoScriptRectors = [])
    {
        $this->typoscriptParser = $typoscriptParser;
        $this->output = $output;
        $this->typoscriptPrinter = $typoscriptPrinter;
        $this->currentFileProvider = $currentFileProvider;
        $this->editorConfigParser = $editorConfigParser;
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
        $this->rectorOutputStyle = $rectorOutputStyle;
        $this->typoScriptRectors = $typoScriptRectors;
    }
    /**
     * @param \Rector\Core\ValueObject\Application\File $file
     * @param \Rector\Core\ValueObject\Configuration $configuration
     */
    public function supports($file, $configuration) : bool
    {
        if ([] === $this->typoScriptRectors) {
            return \false;
        }
        $smartFileInfo = $file->getSmartFileInfo();
        return \in_array($smartFileInfo->getExtension(), $this->allowedFileExtensions, \true);
    }
    /**
     * @param \Rector\Core\ValueObject\Application\File $file
     * @param \Rector\Core\ValueObject\Configuration $configuration
     */
    public function process($file, $configuration) : void
    {
        $this->processFile($file);
        $this->convertTypoScriptToPhpFiles();
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
    public function configure($configuration) : void
    {
        $this->allowedFileExtensions = $configuration[self::ALLOWED_FILE_EXTENSIONS] ?? [];
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
            $prettyPrinterConfiguration = \RectorPrefix20211020\Helmich\TypoScriptParser\Parser\Printer\PrettyPrinterConfiguration::create();
            $prettyPrinterConfiguration = $prettyPrinterConfiguration->withEmptyLineBreaks();
            if ('tab' === $editorConfiguration->getIndentStyle()) {
                $prettyPrinterConfiguration = $prettyPrinterConfiguration->withTabs();
            } else {
                $prettyPrinterConfiguration = $prettyPrinterConfiguration->withSpaceIndentation($editorConfiguration->getIndentSize());
            }
            $prettyPrinterConfiguration = $prettyPrinterConfiguration->withClosingGlobalStatement();
            $this->typoscriptPrinter->setPrettyPrinterConfiguration($prettyPrinterConfiguration);
            $this->typoscriptPrinter->printStatements($originalStatements, $this->output);
            $typoScriptContent = \rtrim($this->output->fetch()) . $editorConfiguration->getNewLine();
            $file->changeFileContent($typoScriptContent);
        } catch (\RectorPrefix20211020\Helmich\TypoScriptParser\Tokenizer\TokenizerException $exception) {
            return;
        } catch (\RectorPrefix20211020\Helmich\TypoScriptParser\Parser\ParseError $exception) {
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
        return \array_filter($this->typoScriptRectors, function (\RectorPrefix20211020\Helmich\TypoScriptParser\Parser\Traverser\Visitor $visitor) : bool {
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
}
