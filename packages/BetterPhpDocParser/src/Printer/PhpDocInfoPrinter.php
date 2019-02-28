<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Printer;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwarePhpDocNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\Attribute;
use Rector\BetterPhpDocParser\Attributes\Contract\Ast\AttributeAwareNodeInterface;
use Rector\BetterPhpDocParser\Data\StartEndInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;

final class PhpDocInfoPrinter
{
    /**
     * @var int
     */
    private $tokenCount;

    /**
     * @var int
     */
    private $currentTokenPosition;

    /**
     * @var mixed[]
     */
    private $tokens = [];

    /**
     * @var StartEndInfo[]
     */
    private $removedNodePositions = [];

    /**
     * @var AttributeAwarePhpDocNode
     */
    private $attributeAwarePhpDocNode;

    /**
     * @var OriginalSpacingRestorer
     */
    private $originalSpacingRestorer;

    /**
     * @var PhpDocInfo
     */
    private $phpDocInfo;

    public function __construct(OriginalSpacingRestorer $originalSpacingRestorer)
    {
        $this->originalSpacingRestorer = $originalSpacingRestorer;
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
    public function printFormatPreserving(PhpDocInfo $phpDocInfo): string
    {
        $this->attributeAwarePhpDocNode = $phpDocInfo->getPhpDocNode();
        $this->tokens = $phpDocInfo->getTokens();
        $this->tokenCount = count($phpDocInfo->getTokens());
        $this->phpDocInfo = $phpDocInfo;

        $this->currentTokenPosition = 0;
        $this->removedNodePositions = [];

        return $this->printPhpDocNode($this->attributeAwarePhpDocNode);
    }

    private function printPhpDocNode(AttributeAwarePhpDocNode $attributeAwarePhpDocNode): string
    {
        // no nodes were, so empty doc
        if ($this->isPhpDocNodeEmpty($attributeAwarePhpDocNode)) {
            return '';
        }

        $this->currentTokenPosition = 0;

        $output = '';

        // node output
        $nodeCount = count($attributeAwarePhpDocNode->children);
        foreach ($attributeAwarePhpDocNode->children as $i => $phpDocChildNode) {
            $output .= $this->printNode($phpDocChildNode, null, $i + 1, $nodeCount);
        }

        return $this->printEnd($output);
    }

    private function isPhpDocNodeEmpty(PhpDocNode $phpDocNode): bool
    {
        if (count($phpDocNode->children) === 0) {
            return true;
        }

        foreach ($phpDocNode->children as $phpDocChildNode) {
            if ($phpDocChildNode instanceof PhpDocTextNode) {
                if ($phpDocChildNode->text) {
                    return false;
                }
            } else {
                return false;
            }
        }

        return true;
    }

    private function printNode(
        AttributeAwareNodeInterface $attributeAwareNode,
        ?StartEndInfo $startEndInfo = null,
        int $i = 0,
        int $nodeCount = 0
    ): string {
        $output = '';

        /** @var StartEndInfo|null $tokenStartEndInfo */
        $startEndInfo = $attributeAwareNode->getAttribute(Attribute::PHP_DOC_NODE_INFO) ?: $startEndInfo;

        if ($startEndInfo) {
            $isLastToken = ($nodeCount === $i);

            $output = $this->addTokensFromTo(
                $output,
                $this->currentTokenPosition,
                $startEndInfo->getStart(),
                $isLastToken
            );

            $this->currentTokenPosition = $startEndInfo->getEnd();
        }

        if ($attributeAwareNode instanceof PhpDocTagNode && $startEndInfo) {
            return $this->printPhpDocTagNode($attributeAwareNode, $startEndInfo, $output);
        }

        if ($attributeAwareNode instanceof PhpDocTagNode) {
            return $output . PHP_EOL . '     * ' . (string) $attributeAwareNode;
        }

        if (! $attributeAwareNode instanceof PhpDocTextNode && ! $attributeAwareNode instanceof GenericTagValueNode && $startEndInfo) {
            return $this->originalSpacingRestorer->restoreInOutputWithTokensStartAndEndPosition(
                (string) $attributeAwareNode,
                $this->tokens,
                $startEndInfo
            );
        }

        return $output . (string) $attributeAwareNode;
    }

    private function printEnd(string $output): string
    {
        $lastTokenPosition = $this->attributeAwarePhpDocNode->getAttribute(
            Attribute::LAST_TOKEN_POSITION
        ) ?: $this->currentTokenPosition;

        return $this->addTokensFromTo($output, $lastTokenPosition, $this->tokenCount, true);
    }

    private function addTokensFromTo(
        string $output,
        int $from,
        int $to,
        bool $shouldSkipEmptyLinesAbove = false
    ): string {
        // skip removed nodes
        $positionJumpSet = [];
        foreach ($this->getRemovedNodesPositions() as $removedTokensPosition) {
            $positionJumpSet[$removedTokensPosition->getStart()] = $removedTokensPosition->getEnd();
        }

        // include also space before, in case of inlined docs
        if (isset($this->tokens[$from - 1]) && $this->tokens[$from - 1][1] === Lexer::TOKEN_HORIZONTAL_WS) {
            --$from;
        }

        if ($shouldSkipEmptyLinesAbove) {
            // skip extra empty lines above if this is the last one
            if (Strings::contains($this->tokens[$from][0], PHP_EOL) && Strings::contains(
                $this->tokens[$from + 1][0],
                PHP_EOL
            )) {
                ++$from;
            }
        }

        return $this->appendToOutput($output, $from, $to, $positionJumpSet);
    }

    private function printPhpDocTagNode(
        PhpDocTagNode $phpDocTagNode,
        StartEndInfo $startEndInfo,
        string $output
    ): string {
        $output .= $phpDocTagNode->name;

        $nodeOutput = $this->printNode($phpDocTagNode->value, $startEndInfo);

        if ($nodeOutput && $this->isTagSeparatedBySpace($nodeOutput, $phpDocTagNode)) {
            $output .= ' ';
        }

        return $output . $nodeOutput;
    }

    /**
     * @return StartEndInfo[]
     */
    private function getRemovedNodesPositions(): array
    {
        if ($this->removedNodePositions) {
            return $this->removedNodePositions;
        }

        /** @var AttributeAwareNodeInterface[] $removedNodes */
        $removedNodes = array_diff(
            $this->phpDocInfo->getOriginalPhpDocNode()->children,
            $this->attributeAwarePhpDocNode->children
        );

        foreach ($removedNodes as $removedNode) {
            $removedPhpDocNodeInfo = $removedNode->getAttribute(Attribute::PHP_DOC_NODE_INFO);

            // change start position to start of the line, so the whole line is removed
            $seekPosition = $removedPhpDocNodeInfo->getStart();
            while ($this->tokens[$seekPosition][1] !== Lexer::TOKEN_HORIZONTAL_WS) {
                --$seekPosition;
            }

            $this->removedNodePositions[] = new StartEndInfo($seekPosition - 1, $removedPhpDocNodeInfo->getEnd());
        }

        return $this->removedNodePositions;
    }

    /**
     * @param int[] $positionJumpSet
     */
    private function appendToOutput(string $output, int $from, int $to, array $positionJumpSet): string
    {
        for ($i = $from; $i < $to; ++$i) {
            while (isset($positionJumpSet[$i])) {
                $i = $positionJumpSet[$i];
            }

            $output .= $this->tokens[$i][0] ?? '';
        }

        return $output;
    }

    /**
     * Covers:
     * - "@Long\Annotation"
     * - "@Route("/", name="homepage")",
     * - "@customAnnotation(value)"
     */
    private function isTagSeparatedBySpace(string $nodeOutput, PhpDocTagNode $phpDocTagNode): bool
    {
        $contentWithoutSpace = $phpDocTagNode->name . $nodeOutput;
        if (Strings::contains($this->phpDocInfo->getOriginalContent(), $contentWithoutSpace)) {
            return false;
        }

        return Strings::contains($this->phpDocInfo->getOriginalContent(), $phpDocTagNode->name . ' ');
    }
}
