<?php

declare(strict_types=1);

namespace Rector\FileSystemRector\Rector;

use Nette\Utils\Strings;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\ParserFactory;
use Rector\Autodiscovery\ValueObject\NodesWithFileDestinationValueObject;
use Rector\Core\Configuration\Configuration;
use Rector\Core\PhpParser\Parser\Parser;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\Core\PhpParser\Printer\FormatPerservingPrinter;
use Rector\Core\Rector\AbstractRector\AbstractRectorTrait;
use Rector\FileSystemRector\Contract\FileSystemRectorInterface;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use Rector\PostRector\Application\PostFileProcessor;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
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
     * @var ParameterProvider
     */
    protected $parameterProvider;

    /**
     * @var Node[]
     */
    private $oldStmts = [];

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
     * @var ParserFactory
     */
    private $parserFactory;

    /**
     * @var PostFileProcessor
     */
    private $postFileProcessor;

    /**
     * @required
     */
    public function autowireAbstractFileSystemRector(
        Parser $parser,
        ParserFactory $parserFactory,
        Lexer $lexer,
        FormatPerservingPrinter $formatPerservingPrinter,
        NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator,
        Configuration $configuration,
        BetterStandardPrinter $betterStandardPrinter,
        ParameterProvider $parameterProvider,
        PostFileProcessor $postFileProcessor
    ): void {
        $this->parser = $parser;
        $this->parserFactory = $parserFactory;
        $this->lexer = $lexer;
        $this->formatPerservingPrinter = $formatPerservingPrinter;
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
        $this->configuration = $configuration;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->parameterProvider = $parameterProvider;
        $this->postFileProcessor = $postFileProcessor;
    }

    /**
     * @return Node[]
     */
    protected function parseFileInfoToNodes(SmartFileInfo $smartFileInfo): array
    {
        $oldStmts = $this->parser->parseFileInfo($smartFileInfo);
        // needed for format preserving
        $this->oldStmts = $oldStmts;

        return $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($oldStmts, $smartFileInfo);
    }

    /**
     * @return Node[]
     */
    protected function parseFileInfoToNodesWithoutScope(SmartFileInfo $smartFileInfo): array
    {
        $oldStmts = $this->parser->parseFileInfo($smartFileInfo);
        $this->oldStmts = $oldStmts;

        return $oldStmts;
    }

    /**
     * @param Node[] $nodes
     */
    protected function printNodesToFilePath(array $nodes, string $fileDestination): void
    {
        $nodes = $this->postFileProcessor->traverse($nodes);

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
        $nodes = $this->postFileProcessor->traverse($nodes);

        // re-index keys from 0
        $nodes = array_values($nodes);

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

        $prettyPrintContent = $this->betterStandardPrinter->prettyPrintFile($nodes);

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
        if ($lastToken && Strings::contains($lastToken[1], "\n")) {
            $prettyPrintContent = trim($prettyPrintContent) . PHP_EOL;
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
