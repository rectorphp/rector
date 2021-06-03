<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript;

use RectorPrefix20210603\Helmich\TypoScriptParser\Parser\ParserInterface;
use RectorPrefix20210603\Helmich\TypoScriptParser\Parser\Printer\ASTPrinterInterface;
use RectorPrefix20210603\Helmich\TypoScriptParser\Parser\Printer\PrettyPrinterConfiguration;
use RectorPrefix20210603\Helmich\TypoScriptParser\Parser\Traverser\Traverser;
use RectorPrefix20210603\Helmich\TypoScriptParser\Parser\Traverser\Visitor;
use RectorPrefix20210603\Helmich\TypoScriptParser\Tokenizer\TokenizerException;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Console\Output\RectorOutputStyle;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\FileFormatter\EditorConfig\EditorConfigParser;
use Rector\FileFormatter\ValueObject\Indent;
use Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder;
use Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\ConvertToPhpFileInterface;
use Ssch\TYPO3Rector\Contract\Processor\ConfigurableProcessorInterface;
use Ssch\TYPO3Rector\FileProcessor\TypoScript\Visitors\AbstractVisitor;
use RectorPrefix20210603\Symfony\Component\Console\Output\BufferedOutput;
/**
 * @see \Ssch\TYPO3Rector\Tests\FileProcessor\TypoScript\TypoScriptProcessorTest
 */
final class TypoScriptProcessor implements \Ssch\TYPO3Rector\Contract\Processor\ConfigurableProcessorInterface
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
     * @var mixed[]
     */
    private $visitors = [];
    /**
     * @param Visitor[] $visitors
     */
    public function __construct(\RectorPrefix20210603\Helmich\TypoScriptParser\Parser\ParserInterface $typoscriptParser, \RectorPrefix20210603\Symfony\Component\Console\Output\BufferedOutput $output, \RectorPrefix20210603\Helmich\TypoScriptParser\Parser\Printer\ASTPrinterInterface $typoscriptPrinter, \Rector\Core\Provider\CurrentFileProvider $currentFileProvider, \Rector\FileFormatter\EditorConfig\EditorConfigParser $editorConfigParser, \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector $removedAndAddedFilesCollector, \Rector\Core\Console\Output\RectorOutputStyle $rectorOutputStyle, array $visitors = [])
    {
        $this->typoscriptParser = $typoscriptParser;
        $this->output = $output;
        $this->typoscriptPrinter = $typoscriptPrinter;
        $this->currentFileProvider = $currentFileProvider;
        $this->editorConfigParser = $editorConfigParser;
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
        $this->rectorOutputStyle = $rectorOutputStyle;
        $this->visitors = $visitors;
    }
    /**
     * @param File[] $files
     */
    public function process(array $files) : void
    {
        foreach ($files as $file) {
            $this->processFile($file);
        }
        $this->convertTypoScriptToPhpFiles();
    }
    public function supports(\Rector\Core\ValueObject\Application\File $file) : bool
    {
        if ([] === $this->visitors) {
            return \false;
        }
        $smartFileInfo = $file->getSmartFileInfo();
        return \in_array($smartFileInfo->getExtension(), $this->allowedFileExtensions, \true);
    }
    /**
     * @return string[]
     */
    public function getSupportedFileExtensions() : array
    {
        return $this->allowedFileExtensions;
    }
    public function configure(array $configuration) : void
    {
        $this->allowedFileExtensions = $configuration[self::ALLOWED_FILE_EXTENSIONS] ?? [];
    }
    private function processFile(\Rector\Core\ValueObject\Application\File $file) : void
    {
        try {
            $this->currentFileProvider->setFile($file);
            $smartFileInfo = $file->getSmartFileInfo();
            $originalStatements = $this->typoscriptParser->parseString($smartFileInfo->getContents());
            $traverser = new \RectorPrefix20210603\Helmich\TypoScriptParser\Parser\Traverser\Traverser($originalStatements);
            foreach ($this->visitors as $visitor) {
                $traverser->addVisitor($visitor);
            }
            $traverser->walk();
            $visitorsChanged = \array_filter($this->visitors, function (\Ssch\TYPO3Rector\FileProcessor\TypoScript\Visitors\AbstractVisitor $visitor) {
                return $visitor->hasChanged();
            });
            if ([] === $visitorsChanged) {
                return;
            }
            $editorConfigConfigurationBuilder = \Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder::create();
            $editorConfigConfigurationBuilder->withIndent(\Rector\FileFormatter\ValueObject\Indent::createSpaceWithSize(4));
            $editorConfiguration = $this->editorConfigParser->extractConfigurationForFile($file, $editorConfigConfigurationBuilder);
            $prettyPrinterConfiguration = \RectorPrefix20210603\Helmich\TypoScriptParser\Parser\Printer\PrettyPrinterConfiguration::create();
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
        } catch (\RectorPrefix20210603\Helmich\TypoScriptParser\Tokenizer\TokenizerException $tokenizerException) {
            return;
        }
    }
    /**
     * @return ConvertToPhpFileInterface[]
     */
    private function convertToPhpFileVisitors() : array
    {
        return \array_filter($this->visitors, function (\RectorPrefix20210603\Helmich\TypoScriptParser\Parser\Traverser\Visitor $visitor) : bool {
            return \is_a($visitor, \Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\ConvertToPhpFileInterface::class, \true);
        });
    }
    private function convertTypoScriptToPhpFiles() : void
    {
        foreach ($this->convertToPhpFileVisitors() as $convertToPhpFileVisitor) {
            $addedFileWithContent = $convertToPhpFileVisitor->convert();
            if (null === $addedFileWithContent) {
                continue;
            }
            $this->removedAndAddedFilesCollector->addAddedFile($addedFileWithContent);
            $this->rectorOutputStyle->warning($convertToPhpFileVisitor->getMessage());
        }
    }
}
