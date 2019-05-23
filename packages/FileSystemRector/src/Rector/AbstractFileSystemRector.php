<?php declare(strict_types=1);

namespace Rector\FileSystemRector\Rector;

use Nette\Utils\Strings;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\ParserFactory;
use Rector\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Configuration\Configuration;
use Rector\FileSystemRector\Contract\FileSystemRectorInterface;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use Rector\PhpParser\Parser\Parser;
use Rector\PhpParser\Printer\BetterStandardPrinter;
use Rector\PhpParser\Printer\FormatPerservingPrinter;
use Rector\Rector\AbstractRectorTrait;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;
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
    public function setAbstractFileSystemRectorDependencies(
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
        $this->oldStmts = $oldStmts;
        // needed for format preserving
        return $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile(
            $oldStmts,
            $smartFileInfo->getRealPath()
        );
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
            $fileContent = $prettyPrintContent;
        }

        $this->addFile($fileDestination, $fileContent);
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
        // remove all comments

        // remove all spaces
        $firstString = Strings::replace($firstString, '#\s+#', '');
        $secondString = Strings::replace($secondString, '#\s+#', '');

        $firstString = $this->removeComments($firstString);
        $secondString = $this->removeComments($secondString);

        // remove FQN "\" that are added by basic printer
        $firstString = Strings::replace($firstString, '#\\\\#', '');
        $secondString = Strings::replace($secondString, '#\\\\#', '');

        return $firstString === $secondString;
    }

    private function removeComments(string $string): string
    {
        $string = Strings::replace($string, '#/\*.*?\*/#s', '');

        return Strings::replace($string, '#\n\s*\n#', "\n");
    }
}
