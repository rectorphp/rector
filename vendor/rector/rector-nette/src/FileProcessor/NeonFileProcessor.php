<?php

declare (strict_types=1);
namespace Rector\Nette\FileProcessor;

use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Nette\Contract\Rector\NeonRectorInterface;
use Rector\Nette\NeonParser\NeonNodeTraverserFactory;
use Rector\Nette\NeonParser\NeonParser;
use Rector\Nette\NeonParser\Printer\FormatPreservingNeonPrinter;
final class NeonFileProcessor implements \Rector\Core\Contract\Processor\FileProcessorInterface
{
    /**
     * @var \Rector\Nette\NeonParser\NeonParser
     */
    private $neonParser;
    /**
     * @var \Rector\Nette\NeonParser\NeonNodeTraverserFactory
     */
    private $neonNodeTraverserFactory;
    /**
     * @var \Rector\Nette\NeonParser\Printer\FormatPreservingNeonPrinter
     */
    private $formatPreservingNeonPrinter;
    /**
     * @var \Rector\Nette\Contract\Rector\NeonRectorInterface[]
     */
    private $neonRectors;
    /**
     * @param NeonRectorInterface[] $neonRectors
     */
    public function __construct(\Rector\Nette\NeonParser\NeonParser $neonParser, \Rector\Nette\NeonParser\NeonNodeTraverserFactory $neonNodeTraverserFactory, \Rector\Nette\NeonParser\Printer\FormatPreservingNeonPrinter $formatPreservingNeonPrinter, array $neonRectors)
    {
        $this->neonParser = $neonParser;
        $this->neonNodeTraverserFactory = $neonNodeTraverserFactory;
        $this->formatPreservingNeonPrinter = $formatPreservingNeonPrinter;
        $this->neonRectors = $neonRectors;
    }
    /**
     * @param \Rector\Core\ValueObject\Application\File $file
     * @param \Rector\Core\ValueObject\Configuration $configuration
     */
    public function process($file, $configuration) : void
    {
        if ($this->neonRectors === []) {
            return;
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
            return;
        }
        $file->changeFileContent($changedFileContent);
    }
    /**
     * @param \Rector\Core\ValueObject\Application\File $file
     * @param \Rector\Core\ValueObject\Configuration $configuration
     */
    public function supports($file, $configuration) : bool
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
