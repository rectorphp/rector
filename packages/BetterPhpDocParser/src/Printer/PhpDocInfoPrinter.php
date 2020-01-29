<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Printer;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\Attribute;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\ValueObject\StartEndValueObject;

/**
 * @see \Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinter\PhpDocInfoPrinterTest
 */
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
     * @var StartEndValueObject[]
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

    /**
     * @var MultilineSpaceFormatPreserver
     */
    private $multilineSpaceFormatPreserver;

    public function __construct(
        OriginalSpacingRestorer $originalSpacingRestorer,
        MultilineSpaceFormatPreserver $multilineSpaceFormatPreserver
    ) {
        $this->originalSpacingRestorer = $originalSpacingRestorer;
        $this->multilineSpaceFormatPreserver = $multilineSpaceFormatPreserver;
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
    public function printFormatPreserving(PhpDocInfo $phpDocInfo, bool $shouldSkipEmptyLinesAbove = false): string
    {
        $this->attributeAwarePhpDocNode = $phpDocInfo->getPhpDocNode();
        $this->tokens = $phpDocInfo->getTokens();

        $this->tokenCount = count($phpDocInfo->getTokens());
        $this->phpDocInfo = $phpDocInfo;

        $this->currentTokenPosition = 0;
        $this->removedNodePositions = [];

        return $this->printPhpDocNode($this->attributeAwarePhpDocNode, $shouldSkipEmptyLinesAbove);
    }

    private function printPhpDocNode(
        AttributeAwarePhpDocNode $attributeAwarePhpDocNode,
        bool $shouldSkipEmptyLinesAbove = false
    ): string {
        // no nodes were, so empty doc
        if ($this->isPhpDocNodeEmpty($attributeAwarePhpDocNode)) {
            return '';
        }

        $this->currentTokenPosition = 0;

        $output = '';

        // node output
        $nodeCount = count($attributeAwarePhpDocNode->children);

        foreach ($attributeAwarePhpDocNode->children as $i => $phpDocChildNode) {
            $output .= $this->printNode($phpDocChildNode, null, $i + 1, $nodeCount, $shouldSkipEmptyLinesAbove);
        }

        $output = $this->printEnd($output);

        // @see
        // fix missing start
        if (! Strings::match($output, '#^(\/\/|\/\*\*|\/\*)#') && $output) {
            $output = '/**' . $output;
        }

        return $output;
    }

    private function isPhpDocNodeEmpty(PhpDocNode $phpDocNode): bool
    {
        if (count($phpDocNode->children) === 0) {
            return true;
        }

        foreach ($phpDocNode->children as $phpDocChildNode) {
            if ($phpDocChildNode instanceof PhpDocTextNode) {
                if ($phpDocChildNode->text !== '') {
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
        ?StartEndValueObject $startEndValueObject = null,
        int $i = 0,
        int $nodeCount = 0,
        bool $shouldSkipEmptyLinesAbove = false
    ): string {
        $output = '';

        /** @var StartEndValueObject|null $startEndValueObject */
        $startEndValueObject = $attributeAwareNode->getAttribute(Attribute::START_END) ?: $startEndValueObject;
        $attributeAwareNode = $this->multilineSpaceFormatPreserver->fixMultilineDescriptions($attributeAwareNode);

        if ($startEndValueObject !== null) {
            $isLastToken = ($nodeCount === $i);
            $output = $this->addTokensFromTo(
                $output,
                $this->currentTokenPosition,
                $startEndValueObject->getStart(),
                ! $shouldSkipEmptyLinesAbove && $isLastToken
            );

            $this->currentTokenPosition = $startEndValueObject->getEnd();
        }

        if ($attributeAwareNode instanceof PhpDocTagNode) {
            if ($startEndValueObject !== null) {
                return $this->printPhpDocTagNode($attributeAwareNode, $startEndValueObject, $output);
            }

            return $output . PHP_EOL . ' * ' . $this->printAttributeWithAsterisk($attributeAwareNode);
        }

        if (! $attributeAwareNode instanceof PhpDocTextNode && ! $attributeAwareNode instanceof GenericTagValueNode && $startEndValueObject) {
            return $this->originalSpacingRestorer->restoreInOutputWithTokensStartAndEndPosition(
                $attributeAwareNode,
                (string) $attributeAwareNode,
                $this->tokens,
                $startEndValueObject
            );
        }

        return $output . $this->printAttributeWithAsterisk($attributeAwareNode);
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

        // skip extra empty lines above if this is the last one
        if ($shouldSkipEmptyLinesAbove &&
            Strings::contains($this->tokens[$from][0], PHP_EOL) &&
            Strings::contains($this->tokens[$from + 1][0], PHP_EOL)
        ) {
            ++$from;
        }

        return $this->appendToOutput($output, $from, $to, $positionJumpSet);
    }

    /**
     * @param PhpDocTagNode|AttributeAwareNodeInterface $phpDocTagNode
     */
    private function printPhpDocTagNode(
        PhpDocTagNode $phpDocTagNode,
        StartEndValueObject $startEndValueObject,
        string $output
    ): string {
        $output .= $phpDocTagNode->name;
        $nodeOutput = $this->printNode($phpDocTagNode->value, $startEndValueObject);
        if ($nodeOutput && $this->isTagSeparatedBySpace($nodeOutput, $phpDocTagNode)) {
            $output .= ' ';
        }

        if ($phpDocTagNode->getAttribute(Attribute::HAS_DESCRIPTION_WITH_ORIGINAL_SPACES) && (property_exists(
            $phpDocTagNode->value,
            'description'
        ) && $phpDocTagNode->value->description)) {
            $quotedDescription = preg_quote($phpDocTagNode->value->description, '#');
            $pattern = Strings::replace($quotedDescription, '#[\s]+#', '\s+');
            $nodeOutput = Strings::replace($nodeOutput, '#' . $pattern . '#', $phpDocTagNode->value->description);
            if (substr_count($nodeOutput, "\n") !== 0) {
                $nodeOutput = Strings::replace($nodeOutput, "#\n#", PHP_EOL . '  * ');
            }
        }

        return $output . $nodeOutput;
    }

    private function printAttributeWithAsterisk(AttributeAwareNodeInterface $attributeAwareNode): string
    {
        $content = (string) $attributeAwareNode;
        $content = explode(PHP_EOL, $content);

        return implode(PHP_EOL . ' * ', $content);
    }

    /**
     * @return StartEndValueObject[]
     */
    private function getRemovedNodesPositions(): array
    {
        if ($this->removedNodePositions !== []) {
            return $this->removedNodePositions;
        }

        /** @var AttributeAwareNodeInterface[] $removedNodes */
        $removedNodes = array_diff(
            $this->phpDocInfo->getOriginalPhpDocNode()->children,
            $this->attributeAwarePhpDocNode->children
        );

        foreach ($removedNodes as $removedNode) {
            $removedPhpDocNodeInfo = $removedNode->getAttribute(Attribute::START_END);

            // change start position to start of the line, so the whole line is removed
            $seekPosition = $removedPhpDocNodeInfo->getStart();
            while ($this->tokens[$seekPosition][1] !== Lexer::TOKEN_HORIZONTAL_WS) {
                --$seekPosition;
            }

            $this->removedNodePositions[] = new StartEndValueObject(
                $seekPosition - 1,
                $removedPhpDocNodeInfo->getEnd()
            );
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
        $contentWithoutSpace = $phpDocTagNode->name . Strings::substring($nodeOutput, 0, 1);
        if (Strings::contains($this->phpDocInfo->getOriginalContent(), $contentWithoutSpace)) {
            return false;
        }

        return Strings::contains($this->phpDocInfo->getOriginalContent(), $phpDocTagNode->name . ' ');
    }
}
