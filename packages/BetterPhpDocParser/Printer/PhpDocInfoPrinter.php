<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\Printer;

use RectorPrefix20211020\Nette\Utils\Strings;
use PhpParser\Node\Stmt\InlineHTML;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocNodeTraverser\ChangedPhpDocNodeTraverserFactory;
use Rector\BetterPhpDocParser\PhpDocNodeVisitor\ChangedPhpDocNodeVisitor;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\BetterPhpDocParser\ValueObject\StartAndEnd;
use Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20211020\Symplify\SimplePhpDocParser\PhpDocNodeTraverser;
/**
 * @see \Rector\Tests\BetterPhpDocParser\PhpDocInfo\PhpDocInfoPrinter\PhpDocInfoPrinterTest
 */
final class PhpDocInfoPrinter
{
    /**
     * @var string
     * @see https://regex101.com/r/Ab0Vey/1
     */
    public const CLOSING_DOCBLOCK_REGEX = '#\\*\\/(\\s+)?$#';
    /**
     * @var string
     * @see https://regex101.com/r/mVmOCY/2
     */
    private const OPENING_DOCBLOCK_REGEX = '#^(/\\*\\*)#';
    /**
     * @var string
     * @see https://regex101.com/r/5fJyws/1
     */
    private const CALLABLE_REGEX = '#callable(\\s+)\\(#';
    /**
     * @var string
     * @see https://regex101.com/r/LLWiPl/1
     */
    private const DOCBLOCK_START_REGEX = '#^(\\/\\/|\\/\\*\\*|\\/\\*|\\#)#';
    /**
     * @var string Uses a hardcoded unix-newline since most codes use it (even on windows) - otherwise we would need to normalize newlines
     */
    private const NEWLINE_WITH_ASTERISK = "\n" . ' *';
    /**
     * @see https://regex101.com/r/WR3goY/1/
     * @var string
     */
    private const TAG_AND_SPACE_REGEX = '#(@.*?) \\(#';
    /**
     * @var int
     */
    private $tokenCount = 0;
    /**
     * @var int
     */
    private $currentTokenPosition = 0;
    /**
     * @var mixed[]
     */
    private $tokens = [];
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo|null
     */
    private $phpDocInfo;
    /**
     * @var \Symplify\SimplePhpDocParser\PhpDocNodeTraverser
     */
    private $changedPhpDocNodeTraverser;
    /**
     * @var \Rector\BetterPhpDocParser\Printer\EmptyPhpDocDetector
     */
    private $emptyPhpDocDetector;
    /**
     * @var \Rector\BetterPhpDocParser\Printer\DocBlockInliner
     */
    private $docBlockInliner;
    /**
     * @var \Rector\BetterPhpDocParser\Printer\RemoveNodesStartAndEndResolver
     */
    private $removeNodesStartAndEndResolver;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocNodeVisitor\ChangedPhpDocNodeVisitor
     */
    private $changedPhpDocNodeVisitor;
    public function __construct(\Rector\BetterPhpDocParser\Printer\EmptyPhpDocDetector $emptyPhpDocDetector, \Rector\BetterPhpDocParser\Printer\DocBlockInliner $docBlockInliner, \Rector\BetterPhpDocParser\Printer\RemoveNodesStartAndEndResolver $removeNodesStartAndEndResolver, \Rector\BetterPhpDocParser\PhpDocNodeVisitor\ChangedPhpDocNodeVisitor $changedPhpDocNodeVisitor, \Rector\BetterPhpDocParser\PhpDocNodeTraverser\ChangedPhpDocNodeTraverserFactory $changedPhpDocNodeTraverserFactory)
    {
        $this->emptyPhpDocDetector = $emptyPhpDocDetector;
        $this->docBlockInliner = $docBlockInliner;
        $this->removeNodesStartAndEndResolver = $removeNodesStartAndEndResolver;
        $this->changedPhpDocNodeVisitor = $changedPhpDocNodeVisitor;
        $this->changedPhpDocNodeTraverser = $changedPhpDocNodeTraverserFactory->create();
    }
    public function printNew(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : string
    {
        $docContent = (string) $phpDocInfo->getPhpDocNode();
        if ($phpDocInfo->isSingleLine()) {
            return $this->docBlockInliner->inline($docContent);
        }
        if ($phpDocInfo->getNode() instanceof \PhpParser\Node\Stmt\InlineHTML) {
            return '<?php' . \PHP_EOL . $docContent . \PHP_EOL . '?>';
        }
        return $docContent;
    }
    /**
     * As in php-parser
     *
     * ref: https://github.com/nikic/PHP-Parser/issues/487#issuecomment-375986259
     * - Tokens[node.startPos .. subnode1.startPos]
     * - Print(subnode1)
     * - Tokens[subnode1.endPos .. subnode2.startPos]
     * - Print(subnode2)
     * - Tokens[subnode2.endPos .. node.endPos]
     */
    public function printFormatPreserving(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : string
    {
        if ($phpDocInfo->getTokens() === []) {
            // completely new one, just print string version of it
            if ($phpDocInfo->getPhpDocNode()->children === []) {
                return '';
            }
            if ($phpDocInfo->getNode() instanceof \PhpParser\Node\Stmt\InlineHTML) {
                return '<?php' . \PHP_EOL . $phpDocInfo->getPhpDocNode() . \PHP_EOL . '?>';
            }
            return (string) $phpDocInfo->getPhpDocNode();
        }
        $phpDocNode = $phpDocInfo->getPhpDocNode();
        $this->tokens = $phpDocInfo->getTokens();
        $this->tokenCount = $phpDocInfo->getTokenCount();
        $this->phpDocInfo = $phpDocInfo;
        $this->currentTokenPosition = 0;
        $phpDocString = $this->printPhpDocNode($phpDocNode);
        // hotfix of extra space with callable ()
        return \RectorPrefix20211020\Nette\Utils\Strings::replace($phpDocString, self::CALLABLE_REGEX, 'callable(');
    }
    public function getCurrentPhpDocInfo() : \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo
    {
        if ($this->phpDocInfo === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        return $this->phpDocInfo;
    }
    private function printPhpDocNode(\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode $phpDocNode) : string
    {
        // no nodes were, so empty doc
        if ($this->emptyPhpDocDetector->isPhpDocNodeEmpty($phpDocNode)) {
            return '';
        }
        $output = '';
        // node output
        $nodeCount = \count($phpDocNode->children);
        foreach ($phpDocNode->children as $key => $phpDocChildNode) {
            $output .= $this->printDocChildNode($phpDocChildNode, $key + 1, $nodeCount);
        }
        $output = $this->printEnd($output);
        // fix missing start
        if (!\RectorPrefix20211020\Nette\Utils\Strings::match($output, self::DOCBLOCK_START_REGEX) && $output) {
            $output = '/**' . $output;
        }
        // fix missing end
        if (\RectorPrefix20211020\Nette\Utils\Strings::match($output, self::OPENING_DOCBLOCK_REGEX) && $output && !\RectorPrefix20211020\Nette\Utils\Strings::match($output, self::CLOSING_DOCBLOCK_REGEX)) {
            $output .= ' */';
        }
        return $output;
    }
    private function printDocChildNode(\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode $phpDocChildNode, int $key = 0, int $nodeCount = 0) : string
    {
        $output = '';
        $shouldReprintChildNode = $this->shouldReprint($phpDocChildNode);
        if ($phpDocChildNode instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode) {
            if ($shouldReprintChildNode && ($phpDocChildNode->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode || $phpDocChildNode->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode || $phpDocChildNode->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode || $phpDocChildNode->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode || $phpDocChildNode->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode)) {
                // the type has changed → reprint
                $phpDocChildNodeStartEnd = $phpDocChildNode->getAttribute(\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey::START_AND_END);
                // bump the last position of token after just printed node
                if ($phpDocChildNodeStartEnd instanceof \Rector\BetterPhpDocParser\ValueObject\StartAndEnd) {
                    $this->currentTokenPosition = $phpDocChildNodeStartEnd->getEnd();
                }
                return $this->standardPrintPhpDocChildNode($phpDocChildNode);
            }
            if ($phpDocChildNode->value instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode && $shouldReprintChildNode) {
                $printedNode = (string) $phpDocChildNode;
                // remove extra space between tags
                $printedNode = \RectorPrefix20211020\Nette\Utils\Strings::replace($printedNode, self::TAG_AND_SPACE_REGEX, '$1(');
                return self::NEWLINE_WITH_ASTERISK . ($printedNode === '' ? '' : ' ' . $printedNode);
            }
        }
        /** @var StartAndEnd|null $startAndEnd */
        $startAndEnd = $phpDocChildNode->getAttribute(\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey::START_AND_END);
        if ($startAndEnd instanceof \Rector\BetterPhpDocParser\ValueObject\StartAndEnd && !$shouldReprintChildNode) {
            $isLastToken = $nodeCount === $key;
            // correct previously changed node
            $this->correctPreviouslyReprintedFirstNode($key, $startAndEnd);
            $output = $this->addTokensFromTo($output, $this->currentTokenPosition, $startAndEnd->getEnd(), $isLastToken);
            $this->currentTokenPosition = $startAndEnd->getEnd();
            return \rtrim($output);
        }
        if ($startAndEnd instanceof \Rector\BetterPhpDocParser\ValueObject\StartAndEnd) {
            $this->currentTokenPosition = $startAndEnd->getEnd();
        }
        $standardPrintedPhpDocChildNode = $this->standardPrintPhpDocChildNode($phpDocChildNode);
        return $output . $standardPrintedPhpDocChildNode;
    }
    private function printEnd(string $output) : string
    {
        $lastTokenPosition = $this->getCurrentPhpDocInfo()->getPhpDocNode()->getAttribute(\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey::LAST_PHP_DOC_TOKEN_POSITION) ?: $this->currentTokenPosition;
        if ($lastTokenPosition === 0) {
            $lastTokenPosition = 1;
        }
        return $this->addTokensFromTo($output, $lastTokenPosition, $this->tokenCount, \true);
    }
    private function addTokensFromTo(string $output, int $from, int $to, bool $shouldSkipEmptyLinesAbove) : string
    {
        // skip removed nodes
        $positionJumpSet = [];
        $removedStartAndEnds = $this->removeNodesStartAndEndResolver->resolve($this->getCurrentPhpDocInfo()->getOriginalPhpDocNode(), $this->getCurrentPhpDocInfo()->getPhpDocNode(), $this->tokens);
        foreach ($removedStartAndEnds as $removedStartAndEnd) {
            $positionJumpSet[$removedStartAndEnd->getStart()] = $removedStartAndEnd->getEnd();
        }
        // include also space before, in case of inlined docs
        if (isset($this->tokens[$from - 1]) && $this->tokens[$from - 1][1] === \PHPStan\PhpDocParser\Lexer\Lexer::TOKEN_HORIZONTAL_WS) {
            --$from;
        }
        // skip extra empty lines above if this is the last one
        if ($shouldSkipEmptyLinesAbove && \strpos($this->tokens[$from][0], \PHP_EOL) !== \false && \strpos($this->tokens[$from + 1][0], \PHP_EOL) !== \false) {
            ++$from;
        }
        return $this->appendToOutput($output, $from, $to, $positionJumpSet);
    }
    /**
     * @param array<int, int> $positionJumpSet
     */
    private function appendToOutput(string $output, int $from, int $to, array $positionJumpSet) : string
    {
        for ($i = $from; $i < $to; ++$i) {
            while (isset($positionJumpSet[$i])) {
                $i = $positionJumpSet[$i];
                continue;
            }
            $output .= $this->tokens[$i][0] ?? '';
        }
        return $output;
    }
    private function correctPreviouslyReprintedFirstNode(int $key, \Rector\BetterPhpDocParser\ValueObject\StartAndEnd $startAndEnd) : void
    {
        if ($this->currentTokenPosition !== 0) {
            return;
        }
        if ($key === 1) {
            return;
        }
        $startTokenPosition = $startAndEnd->getStart();
        $tokens = $this->getCurrentPhpDocInfo()->getTokens();
        if (!isset($tokens[$startTokenPosition - 1])) {
            return;
        }
        $previousToken = $tokens[$startTokenPosition - 1];
        if ($previousToken[1] === \PHPStan\PhpDocParser\Lexer\Lexer::TOKEN_PHPDOC_EOL) {
            --$startTokenPosition;
        }
        $this->currentTokenPosition = $startTokenPosition;
    }
    private function shouldReprint(\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode $phpDocChildNode) : bool
    {
        $this->changedPhpDocNodeTraverser->traverse($phpDocChildNode);
        return $this->changedPhpDocNodeVisitor->hasChanged();
    }
    private function standardPrintPhpDocChildNode(\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode $phpDocChildNode) : string
    {
        $printedNode = (string) $phpDocChildNode;
        if ($this->getCurrentPhpDocInfo()->isSingleLine()) {
            return ' ' . $printedNode;
        }
        return self::NEWLINE_WITH_ASTERISK . ($printedNode === '' ? '' : ' ' . $printedNode);
    }
}
