<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\Printer;

use RectorPrefix202610\Nette\Utils\Strings;
use PhpParser\Comment;
use PhpParser\Node\Stmt\InlineHTML;
use PHPStan\PhpDocParser\Ast\Attribute;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\NodeTraverser;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Printer\Printer;
use Rector\BetterPhpDocParser\PhpDoc\SpacelessPhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocNodeVisitor\ChangedPhpDocNodeVisitor;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\BetterPhpDocParser\ValueObject\StartAndEnd;
/**
 * @see \Rector\Tests\BetterPhpDocParser\PhpDocInfo\PhpDocInfoPrinter\PhpDocInfoPrinterTest
 */
final class PhpDocInfoPrinter
{
    /**
     * @readonly
     */
    private Printer $printer;
    /**
     * @readonly
     */
    private \Rector\BetterPhpDocParser\Printer\EmptyPhpDocDetector $emptyPhpDocDetector;
    /**
     * @readonly
     */
    private \Rector\BetterPhpDocParser\Printer\DocBlockInliner $docBlockInliner;
    /**
     * @readonly
     */
    private ChangedPhpDocNodeVisitor $changedPhpDocNodeVisitor;
    /**
     * @see https://regex101.com/r/ME5Fcn/1
     * @var string
     */
    private const NEW_LINE_WITH_SPACE_REGEX = "# (?<new_line>\r\n|\n)#";
    /**
     * @readonly
     */
    private NodeTraverser $changedPhpDocNodeTraverser;
    public function __construct(Printer $printer, \Rector\BetterPhpDocParser\Printer\EmptyPhpDocDetector $emptyPhpDocDetector, \Rector\BetterPhpDocParser\Printer\DocBlockInliner $docBlockInliner, ChangedPhpDocNodeVisitor $changedPhpDocNodeVisitor)
    {
        $this->printer = $printer;
        $this->emptyPhpDocDetector = $emptyPhpDocDetector;
        $this->docBlockInliner = $docBlockInliner;
        $this->changedPhpDocNodeVisitor = $changedPhpDocNodeVisitor;
        $this->changedPhpDocNodeTraverser = new NodeTraverser([$this->changedPhpDocNodeVisitor]);
    }
    public function printNew(PhpDocInfo $phpDocInfo): string
    {
        $docContent = $this->printFresh($phpDocInfo->getPhpDocNode());
        if ($phpDocInfo->isSingleLine()) {
            return $this->docBlockInliner->inline($docContent);
        }
        if ($phpDocInfo->getNode() instanceof InlineHTML) {
            return '<?php' . \PHP_EOL . $docContent . \PHP_EOL . '?>';
        }
        return $docContent;
    }
    public function printFormatPreserving(PhpDocInfo $phpDocInfo): string
    {
        $phpDocNode = $phpDocInfo->getPhpDocNode();
        if ($this->emptyPhpDocDetector->isPhpDocNodeEmpty($phpDocNode)) {
            return '';
        }
        $tokenIterator = $phpDocInfo->getTokenIterator();
        // completely new one, or a tag was added to a single-line docblock
        if ($tokenIterator->getTokenCount() === 0 || !$phpDocInfo->isSingleLine() && !$this->hasNewLine($tokenIterator)) {
            $docContent = $this->printFresh($phpDocNode);
            if ($phpDocInfo->getNode() instanceof InlineHTML) {
                return '<?php' . \PHP_EOL . $docContent . \PHP_EOL . '?>';
            }
            return $docContent;
        }
        $this->reindexArrays($phpDocNode);
        $originalPhpDocNode = $phpDocInfo->getOriginalPhpDocNode();
        $phpDocNode = $this->removeTrailingEmptyLines($phpDocNode, $originalPhpDocNode);
        [$phpDocNode, $originalPhpDocNode] = $this->replaceOpaqueChildren($phpDocNode, $originalPhpDocNode, $tokenIterator);
        $docContent = $this->printer->printFormatPreserving($phpDocNode, $originalPhpDocNode, $tokenIterator);
        return Strings::replace($docContent, self::NEW_LINE_WITH_SPACE_REGEX, static fn(array $match): string => (string) $match['new_line']);
    }
    /**
     * @return Comment[]
     */
    public function printToComments(PhpDocInfo $phpDocInfo): array
    {
        $printedPhpDocContents = $this->printFormatPreserving($phpDocInfo);
        return [new Comment($printedPhpDocContents)];
    }
    /**
     * The printer compares lists of nodes; unset() in rules leaves gaps in the keys
     */
    private function reindexArrays(Node $node): void
    {
        foreach (get_object_vars($node) as $propertyName => $value) {
            if ($value instanceof Node) {
                $this->reindexArrays($value);
                continue;
            }
            if (!is_array($value)) {
                continue;
            }
            $arrayIsListFunction = function (array $array): bool {
                if (function_exists('array_is_list')) {
                    return array_is_list($array);
                }
                if ($array === []) {
                    return \true;
                }
                $current_key = 0;
                foreach ($array as $key => $noop) {
                    if ($key !== $current_key) {
                        return \false;
                    }
                    ++$current_key;
                }
                return \true;
            };
            if (!$arrayIsListFunction($value)) {
                $node->{$propertyName} = array_values($value);
            }
            foreach ($value as $item) {
                if ($item instanceof Node) {
                    $this->reindexArrays($item);
                }
            }
        }
    }
    /**
     * Removing the last tag keeps the empty line above it, remove it too
     */
    private function removeTrailingEmptyLines(PhpDocNode $phpDocNode, PhpDocNode $originalPhpDocNode): PhpDocNode
    {
        $lastOriginalChildNode = $originalPhpDocNode->children[array_key_last($originalPhpDocNode->children)] ?? null;
        if ($this->isEmptyLine($lastOriginalChildNode)) {
            return $phpDocNode;
        }
        $children = $phpDocNode->children;
        while ($children !== [] && $this->isEmptyLine($children[array_key_last($children)])) {
            array_pop($children);
        }
        if (count($children) === count($phpDocNode->children)) {
            return $phpDocNode;
        }
        return new PhpDocNode($children);
    }
    private function isEmptyLine(?PhpDocChildNode $phpDocChildNode): bool
    {
        return $phpDocChildNode instanceof PhpDocTextNode && trim($phpDocChildNode->text) === '';
    }
    private function printFresh(PhpDocNode $phpDocNode): string
    {
        $children = [];
        foreach ($phpDocNode->children as $phpDocChildNode) {
            $children[] = $phpDocChildNode instanceof SpacelessPhpDocTagNode ? new PhpDocTextNode((string) $phpDocChildNode) : $phpDocChildNode;
        }
        return $this->printer->print(new PhpDocNode($children));
    }
    /**
     * The phpdoc-parser printer cannot print Rector Doctrine annotation nodes, and nodes without token positions.
     * Such child is printed as text: the original tokens when it did not change, or its string value when it did.
     *
     * @return array{PhpDocNode, PhpDocNode}
     */
    private function replaceOpaqueChildren(PhpDocNode $phpDocNode, PhpDocNode $originalPhpDocNode, TokenIterator $tokenIterator): array
    {
        /** @var array<int, PhpDocTextNode> $originalTextNodes */
        $originalTextNodes = [];
        $originalChildren = [];
        foreach ($originalPhpDocNode->children as $originalChildNode) {
            $startAndEnd = $originalChildNode->getAttribute(PhpDocAttributeKey::START_AND_END);
            if (!$startAndEnd instanceof StartAndEnd || !$this->isOpaque($originalChildNode)) {
                $originalChildren[] = $originalChildNode;
                continue;
            }
            $originalTextNode = new PhpDocTextNode(rtrim($tokenIterator->getContentBetween($startAndEnd->getStart(), $startAndEnd->getEnd())));
            $originalTextNode->setAttribute(Attribute::START_INDEX, $startAndEnd->getStart());
            $originalTextNode->setAttribute(Attribute::END_INDEX, $startAndEnd->getEnd() - 1);
            $originalTextNodes[spl_object_id($originalChildNode)] = $originalTextNode;
            $originalChildren[] = $originalTextNode;
        }
        if ($originalTextNodes === [] && !$this->hasSpacelessChild($phpDocNode)) {
            return [$phpDocNode, $originalPhpDocNode];
        }
        $children = [];
        foreach ($phpDocNode->children as $phpDocChildNode) {
            $originalChildNode = $phpDocChildNode->getAttribute(Attribute::ORIGINAL_NODE);
            $originalTextNode = $originalChildNode instanceof Node ? $originalTextNodes[spl_object_id($originalChildNode)] ?? null : null;
            if (!$originalTextNode instanceof PhpDocTextNode) {
                $children[] = $phpDocChildNode instanceof SpacelessPhpDocTagNode ? new PhpDocTextNode((string) $phpDocChildNode) : $phpDocChildNode;
                continue;
            }
            $text = $this->hasChanged($phpDocChildNode) ? $this->printChild($phpDocChildNode) : $originalTextNode->text;
            $textNode = new PhpDocTextNode($text);
            $textNode->setAttribute(Attribute::ORIGINAL_NODE, $originalTextNode);
            $children[] = $textNode;
        }
        return [new PhpDocNode($children), new PhpDocNode($originalChildren)];
    }
    /**
     * Doctrine annotation tags, and nodes that the Doctrine annotation decorator created
     */
    private function isOpaque(PhpDocChildNode $phpDocChildNode): bool
    {
        if ($phpDocChildNode instanceof SpacelessPhpDocTagNode || !$phpDocChildNode->hasAttribute(Attribute::START_INDEX)) {
            return \true;
        }
        return $phpDocChildNode instanceof PhpDocTagNode && !$phpDocChildNode->value->hasAttribute(Attribute::START_INDEX);
    }
    private function hasSpacelessChild(PhpDocNode $phpDocNode): bool
    {
        $found = \false;
        foreach ($phpDocNode->children as $phpDocChildNode) {
            if ($phpDocChildNode instanceof SpacelessPhpDocTagNode) {
                $found = \true;
                break;
            }
        }
        return $found;
    }
    private function printChild(PhpDocChildNode $phpDocChildNode): string
    {
        if ($phpDocChildNode instanceof SpacelessPhpDocTagNode) {
            return (string) $phpDocChildNode;
        }
        return $this->printer->print($phpDocChildNode);
    }
    private function hasChanged(PhpDocChildNode $phpDocChildNode): bool
    {
        $this->changedPhpDocNodeTraverser->traverse([$phpDocChildNode]);
        return $this->changedPhpDocNodeVisitor->hasChanged();
    }
    private function hasNewLine(TokenIterator $tokenIterator): bool
    {
        $found = \false;
        foreach ($tokenIterator->getTokens() as $token) {
            if ($token[Lexer::TYPE_OFFSET] === Lexer::TOKEN_PHPDOC_EOL) {
                $found = \true;
                break;
            }
        }
        return $found;
    }
}
