<?php

declare(strict_types=1);

namespace Rector\FileSystemRector\Rector;

use Nette\Utils\Strings;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\ParserFactory;
use Rector\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Autodiscovery\ValueObject\NodesWithFileDestinationValueObject;
use Rector\Configuration\Configuration;
use Rector\FileSystemRector\Contract\FileSystemRectorInterface;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use Rector\PhpParser\Parser\Parser;
use Rector\PhpParser\Printer\BetterStandardPrinter;
use Rector\PhpParser\Printer\FormatPerservingPrinter;
use Rector\Rector\AbstractRector\AbstractRectorTrait;
use Symplify\SmartFileSystem\SmartFileInfo;
use TypeError;

abstract class AbstractFileSystemRector implements FileSystemRectorInterface
{
    use AbstractRectorTrait;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @var FormatPerservingPrinter
     */
    private $formatPerservingPrinter;

    /**
     * @var NodeScopeAndMetadataDecorator
     */
    private $nodeScopeAndMetadataDecorator;

    /**
     * @var Node[]
     */
    private $oldStmts = [];

    /**
     * @var RemovedAndAddedFilesCollector
     */
    private $removedAndAddedFilesCollector;

    /**
     * @var ParserFactory
     */
    private $parserFactory;

    /**
     * @required
     */
    public function autowireAbstractFileSystemRector(
        Parser $parser,
        ParserFactory $parserFactory,
        Lexer $lexer,
        FormatPerservingPrinter $formatPerservingPrinter,
        NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator,
        RemovedAndAddedFilesCollector $removedAndAddedFilesCollector,
        Configuration $configuration,
        BetterStandardPrinter $betterStandardPrinter
    ): void {
        $this->parser = $parser;
        $this->parserFactory = $parserFactory;
        $this->lexer = $lexer;
        $this->formatPerservingPrinter = $formatPerservingPrinter;
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
        $this->configuration = $configuration;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    /**
     * @return Node[]
     */
    protected function parseFileInfoToNodes(SmartFileInfo $smartFileInfo): array
    {
        $oldStmts = $this->parser->parseFile($smartFileInfo->getRealPath());
        // needed for format preserving
        $this->oldStmts = $oldStmts;
        return $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile(
            $oldStmts,
            $smartFileInfo->getRealPath()
        );
    }

    /**
     * @return Node[]
     */
    protected function parseFileInfoToNodesWithoutScope(SmartFileInfo $smartFileInfo): array
    {
        $oldStmts = $this->parser->parseFile($smartFileInfo->getRealPath());
        $this->oldStmts = $oldStmts;

        return $oldStmts;
    }

    /**
     * @param Node[] $nodes
     */
    protected function printNodesToFilePath(array $nodes, string $fileDestination): void
    {
        $fileContent = $this->formatPerservingPrinter->printToString(
            $nodes,
            $this->oldStmts,
            $this->lexer->getTokens()
        );

        $this->addFile($fileDestination, $fileContent);
    }

    /**
     * @param Node[] $nodes
     */
    protected function printNewNodesToFilePath(array $nodes, string $fileDestination): void
    {
        // 1. if nodes are the same, prefer format preserving printer
        try {
            $dummyLexer = new Lexer();
            $dummyParser = $this->parserFactory->create(ParserFactory::PREFER_PHP7, $dummyLexer);
            $dummyParser->parse('<?php ' . $this->print($nodes));

            $dummyTokenCount = count($dummyLexer->getTokens());
            $modelTokenCount = count($this->lexer->getTokens());

            if ($dummyTokenCount > $modelTokenCount) {
                // nothing we can do - this would end by "Undefined offset in TokenStream.php on line X" error
                $formatPreservingContent = '';
            } else {
                $formatPreservingContent = $this->formatPerservingPrinter->printToString(
                    $nodes,
                    $this->oldStmts,
                    $this->lexer->getTokens()
                );
            }
        } catch (TypeError $typeError) {
            // incompatible tokens, nothing we can do to preserve format
            $formatPreservingContent = '';
        }

        $prettyPrintContent = '<?php' . PHP_EOL . $this->betterStandardPrinter->prettyPrint($nodes);

        if ($this->areStringsSameWithoutSpaces($formatPreservingContent, $prettyPrintContent)) {
            $fileContent = $formatPreservingContent;
        } else {
            $prettyPrintContent = $this->resolveLastEmptyLine($prettyPrintContent);
            $fileContent = $prettyPrintContent;
        }

        $this->addFile($fileDestination, $fileContent);
    }

    protected function printNodesWithFileDestination(
        NodesWithFileDestinationValueObject $nodesWithFileDestinationValueObject
    ): void {
        $this->printNewNodesToFilePath(
            $nodesWithFileDestinationValueObject->getNodes(),
            $nodesWithFileDestinationValueObject->getFileDestination()
        );
    }

    protected function moveFile(SmartFileInfo $oldFileInfo, string $newFileLocation, ?string $fileContent = null): void
    {
        $this->removedAndAddedFilesCollector->addMovedFile($oldFileInfo, $newFileLocation, $fileContent);
    }

    protected function removeFile(SmartFileInfo $smartFileInfo): void
    {
        $this->removedAndAddedFilesCollector->removeFile($smartFileInfo);
    }

    protected function addFile(string $filePath, string $content): void
    {
        $this->removedAndAddedFilesCollector->addFileWithContent($filePath, $content);
    }

    /**
     * Also without FQN "\" that are added by basic printer
     */
    private function areStringsSameWithoutSpaces(string $firstString, string $secondString): bool
    {
        return $this->clearString($firstString) === $this->clearString($secondString);
    }

    /**
     * Add empty line in the end, if it is in the original tokens
     */
    private function resolveLastEmptyLine(string $prettyPrintContent): string
    {
        $tokens = $this->lexer->getTokens();
        $lastToken = array_pop($tokens);
        if ($lastToken) {
            if (Strings::contains($lastToken[1], "\n")) {
                $prettyPrintContent = trim($prettyPrintContent) . PHP_EOL;
            }
        }

        return $prettyPrintContent;
    }

    private function clearString(string $string): string
    {
        $string = $this->removeComments($string);

        // remove all spaces
        $string = Strings::replace($string, '#\s+#', '');

        // remove FQN "\" that are added by basic printer
        $string = Strings::replace($string, '#\\\\#', '');

        // remove trailing commas, as one of them doesn't have to contain them
        return Strings::replace($string, '#\,#', '');
    }

    private function removeComments(string $string): string
    {
        // remove comments like this noe
        $string = Strings::replace($string, '#\/\/(.*?)\n#', '');

        $string = Strings::replace($string, '#/\*.*?\*/#s', '');

        return Strings::replace($string, '#\n\s*\n#', "\n");
    }
}
