<?php

declare (strict_types=1);
namespace Rector\PhpParser\Parser;

use RectorPrefix202409\Nette\Utils\FileSystem;
use PhpParser\Node;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor\AssignedToNodeVisitor;
use Throwable;
final class SimplePhpParser
{
    /**
     * @readonly
     * @var \PhpParser\Parser
     */
    private $phpParser;
    /**
     * @readonly
     * @var \PhpParser\NodeTraverser
     */
    private $nodeTraverser;
    public function __construct()
    {
        $parserFactory = new ParserFactory();
        $this->phpParser = $parserFactory->create(ParserFactory::ONLY_PHP7);
        $this->nodeTraverser = new NodeTraverser();
        $this->nodeTraverser->addVisitor(new AssignedToNodeVisitor());
    }
    /**
     * @api tests
     * @return Node[]
     */
    public function parseFile(string $filePath) : array
    {
        $fileContent = FileSystem::read($filePath);
        return $this->parseString($fileContent);
    }
    /**
     * @return Node[]
     */
    public function parseString(string $fileContent) : array
    {
        $fileContent = $this->ensureFileContentsHasOpeningTag($fileContent);
        $hasAddedSemicolon = \false;
        try {
            $nodes = $this->phpParser->parse($fileContent);
        } catch (Throwable $exception) {
            // try adding missing closing semicolon ;
            $fileContent .= ';';
            $hasAddedSemicolon = \true;
            $nodes = $this->phpParser->parse($fileContent);
        }
        if ($nodes === null) {
            return [];
        }
        $nodes = $this->restoreExpressionPreWrap($nodes, $hasAddedSemicolon);
        return $this->nodeTraverser->traverse($nodes);
    }
    private function ensureFileContentsHasOpeningTag(string $fileContent) : string
    {
        if (\strncmp(\trim($fileContent), '<?php', \strlen('<?php')) !== 0) {
            // prepend with PHP opening tag to make parse PHP code
            return '<?php ' . $fileContent;
        }
        return $fileContent;
    }
    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    private function restoreExpressionPreWrap(array $nodes, bool $hasAddedSemicolon) : array
    {
        if (!$hasAddedSemicolon) {
            return $nodes;
        }
        if (\count($nodes) !== 1) {
            return $nodes;
        }
        // remove added semicolon to be honest about Expression
        $onlyStmt = $nodes[0];
        if (!$onlyStmt instanceof Expression) {
            return $nodes;
        }
        return [$onlyStmt->expr];
    }
}
