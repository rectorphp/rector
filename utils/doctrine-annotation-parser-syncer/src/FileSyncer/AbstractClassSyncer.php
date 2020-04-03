<?php

declare(strict_types=1);

namespace Rector\Utils\DoctrineAnnotationParserSyncer\FileSyncer;

use Nette\Utils\FileSystem;
use PhpParser\Node;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\FileSystemRector\Parser\FileInfoParser;
use Rector\Utils\DoctrineAnnotationParserSyncer\Contract\ClassSyncerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\SmartFileSystem\SmartFileInfo;

abstract class AbstractClassSyncer implements ClassSyncerInterface
{
    /**
     * @var SymfonyStyle
     */
    protected $symfonyStyle;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var FileInfoParser
     */
    private $fileInfoParser;

    /**
     * @required
     */
    public function autowireAbstractClassSyncer(
        BetterStandardPrinter $betterStandardPrinter,
        FileInfoParser $fileInfoParser,
        SymfonyStyle $symfonyStyle
    ): void {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->fileInfoParser = $fileInfoParser;
        $this->symfonyStyle = $symfonyStyle;
    }

    /**
     * @return Node[]
     */
    protected function getFileNodes(): array
    {
        $docParserFileInfo = new SmartFileInfo($this->getSourceFilePath());

        return $this->fileInfoParser->parseFileInfoToNodesAndDecorate($docParserFileInfo);
    }

    /**
     * @param Node[] $nodes
     */
    protected function printNodesToPath(array $nodes): void
    {
        $printedContent = $this->betterStandardPrinter->prettyPrint($nodes);
        $printedContent = '<?php' . PHP_EOL . PHP_EOL . $printedContent;

        FileSystem::write($this->getTargetFilePath(), $printedContent);
    }

    protected function reportChange(): void
    {
        $message = sprintf(
            'Original "%s" was changed and refactored to "%s"',
            $this->getSourceFilePath(),
            $this->getTargetFilePath()
        );
        $this->symfonyStyle->note($message);
    }
}
