<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Printer;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\Attribute;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\ValueObject\StartAndEnd;
use Rector\Core\Exception\ShouldNotHappenException;

/**
 * @see \Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinter\PhpDocInfoPrinterTest
 */
final class PhpDocInfoPrinter
{
    /**
     * @var string
     * @see https://regex101.com/r/Ab0Vey/1
     */
    public const CLOSING_DOCBLOCK_REGEX = '#\*\/(\s+)?$#';

    /**
     * @var string
     */
    private const NEWLINE_ASTERISK = PHP_EOL . ' * ';

    /**
     * @var string
     * @see https://regex101.com/r/mVmOCY/2
     */
    private const OPENING_DOCBLOCK_REGEX = '#^(/\*\*)#';

    /**
     * @var string
     * @see https://regex101.com/r/5fJyws/1
     */
    private const CALLABLE_REGEX = '#callable(\s+)\(#';

    /**
     * @var string
     * @see https://regex101.com/r/LLWiPl/1
     */
    private const DOCBLOCK_START_REGEX = '#^(\/\/|\/\*\*|\/\*|\#)#';

    /**
     * @var string
     * @see https://regex101.com/r/hFwSMz/1
     */
    private const SPACE_AFTER_ASTERISK_REGEX = '#([^*])\*[ \t]+$#sm';

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
     * @var StartAndEnd[]
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

    /**
     * @var SpacePatternFactory
     */
    private $spacePatternFactory;

    /**
     * @var EmptyPhpDocDetector
     */
    private $emptyPhpDocDetector;

    /**
     * @var DocBlockInliner
     */
    private $docBlockInliner;

    public function __construct(
        EmptyPhpDocDetector $emptyPhpDocDetector,
        MultilineSpaceFormatPreserver $multilineSpaceFormatPreserver,
        OriginalSpacingRestorer $originalSpacingRestorer,
        SpacePatternFactory $spacePatternFactory,
        DocBlockInliner $docBlockInliner
    ) {
        $this->originalSpacingRestorer = $originalSpacingRestorer;
        $this->multilineSpaceFormatPreserver = $multilineSpaceFormatPreserver;
        $this->spacePatternFactory = $spacePatternFactory;
        $this->emptyPhpDocDetector = $emptyPhpDocDetector;
        $this->docBlockInliner = $docBlockInliner;
    }

    public function printNew(PhpDocInfo $phpDocInfo): string
    {
        $docContent = (string) $phpDocInfo->getPhpDocNode();
        if ($phpDocInfo->isSingleLine()) {
            return $this->docBlockInliner->inline($docContent);
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
    public function printFormatPreserving(PhpDocInfo $phpDocInfo): string
    {
        if ($phpDocInfo->getTokens() === []) {
            // completely new one, just print string version of it
            if ($phpDocInfo->getPhpDocNode()->children === []) {
                return '';
            }

            return (string) $phpDocInfo->getPhpDocNode();
        }

        $this->attributeAwarePhpDocNode = $phpDocInfo->getPhpDocNode();

        $this->tokens = $phpDocInfo->getTokens();
        $this->tokenCount = $phpDocInfo->getTokenCount();
        $this->phpDocInfo = $phpDocInfo;

        $this->currentTokenPosition = 0;
        $this->removedNodePositions = [];

        $phpDocString = $this->printPhpDocNode($this->attributeAwarePhpDocNode);
        $phpDocString = $this->removeExtraSpacesAfterAsterisk($phpDocString);

        // hotfix of extra space with callable ()
        return Strings::replace($phpDocString, self::CALLABLE_REGEX, 'callable(');
    }

    private function printPhpDocNode(AttributeAwarePhpDocNode $attributeAwarePhpDocNode): string
    {
        // no nodes were, so empty doc
        if ($this->emptyPhpDocDetector->isPhpDocNodeEmpty($attributeAwarePhpDocNode)) {
            return '';
        }

        $this->currentTokenPosition = 0;

        $output = '';

        // node output
        $nodeCount = count($attributeAwarePhpDocNode->children);

        foreach ($attributeAwarePhpDocNode->children as $key => $phpDocChildNode) {
            $output .= $this->printNode($phpDocChildNode, null, $key + 1, $nodeCount);
        }

        $output = $this->printEnd($output);

        // fix missing start
        if (! Strings::match($output, self::DOCBLOCK_START_REGEX) && $output) {
            $output = '/**' . $output;
        }

        // fix missing end
        if (Strings::match($output, self::OPENING_DOCBLOCK_REGEX) && $output && ! Strings::match(
            $output,
            self::CLOSING_DOCBLOCK_REGEX
        )) {
            $output .= ' */';
        }

        return $output;
    }

    private function removeExtraSpacesAfterAsterisk(string $phpDocString): string
    {
        return Strings::replace($phpDocString, self::SPACE_AFTER_ASTERISK_REGEX, '$1*');
    }

    private function printNode(
        AttributeAwareNodeInterface $attributeAwareNode,
        ?StartAndEnd $startAndEnd = null,
        int $key = 0,
        int $nodeCount = 0
    ): string {
        $output = '';

        /** @var StartAndEnd|null $startAndEnd */
        $startAndEnd = $attributeAwareNode->getAttribute(Attribute::START_END) ?: $startAndEnd;
        $attributeAwareNode = $this->multilineSpaceFormatPreserver->fixMultilineDescriptions($attributeAwareNode);

        if ($startAndEnd !== null) {
            $isLastToken = ($nodeCount === $key);

            $output = $this->addTokensFromTo(
                $output,
                $this->currentTokenPosition,
                $startAndEnd->getStart(),
                $isLastToken
            );

            $this->currentTokenPosition = $startAndEnd->getEnd();
        }

        if ($attributeAwareNode instanceof PhpDocTagNode) {
            if ($startAndEnd !== null) {
                return $this->printPhpDocTagNode($attributeAwareNode, $startAndEnd, $output);
            }

            return $output . self::NEWLINE_ASTERISK . $this->printAttributeWithAsterisk($attributeAwareNode);
        }

        if (! $attributeAwareNode instanceof PhpDocTextNode && ! $attributeAwareNode instanceof GenericTagValueNode && $startAndEnd) {
            $nodeContent = (string) $attributeAwareNode;

            return $this->originalSpacingRestorer->restoreInOutputWithTokensStartAndEndPosition(
                $attributeAwareNode,
                $nodeContent,
                $this->tokens,
                $startAndEnd
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
        foreach ($this->getRemovedNodesPositions() as $startAndEnd) {
            $positionJumpSet[$startAndEnd->getStart()] = $startAndEnd->getEnd();
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
        StartAndEnd $startAndEnd,
        string $output
    ): string {
        $output .= $phpDocTagNode->name;

        $phpDocTagNodeValue = $phpDocTagNode->value;
        if (! $phpDocTagNodeValue instanceof AttributeAwareNodeInterface) {
            throw new ShouldNotHappenException();
        }

        $nodeOutput = $this->printNode($phpDocTagNodeValue, $startAndEnd);
        $tagSpaceSeparator = $this->resolveTagSpaceSeparator($phpDocTagNode);

        // space is handled by $tagSpaceSeparator
        $nodeOutput = ltrim($nodeOutput);
        if ($nodeOutput && $tagSpaceSeparator !== '') {
            $output .= $tagSpaceSeparator;
        }

        if ($this->hasDescription($phpDocTagNode)) {
            $quotedDescription = preg_quote($phpDocTagNode->value->description, '#');
            $pattern = Strings::replace($quotedDescription, '#[\s]+#', '\s+');
            $nodeOutput = Strings::replace($nodeOutput, '#' . $pattern . '#', function () use ($phpDocTagNode) {
                // warning: classic string replace() breaks double "\\" slashes to "\"
                return $phpDocTagNode->value->description;
            });

            if (substr_count($nodeOutput, "\n") !== 0) {
                $nodeOutput = Strings::replace($nodeOutput, "#\n#", self::NEWLINE_ASTERISK);
            }
        }

        return $output . $nodeOutput;
    }

    private function printAttributeWithAsterisk(AttributeAwareNodeInterface $attributeAwareNode): string
    {
        $content = (string) $attributeAwareNode;

        return $this->explodeAndImplode($content, PHP_EOL, self::NEWLINE_ASTERISK);
    }

    /**
     * @return StartAndEnd[]
     */
    private function getRemovedNodesPositions(): array
    {
        if ($this->removedNodePositions !== []) {
            return $this->removedNodePositions;
        }

        /** @var AttributeAwareNodeInterface[] $removedNodes */
        $removedNodes = array_diff(
            $this->phpDocInfo->getOriginalPhpDocNode()
                ->children,
            $this->attributeAwarePhpDocNode->children
        );

        foreach ($removedNodes as $removedNode) {
            /** @var StartAndEnd $removedPhpDocNodeInfo */
            $removedPhpDocNodeInfo = $removedNode->getAttribute(Attribute::START_END);

            // change start position to start of the line, so the whole line is removed
            $seekPosition = $removedPhpDocNodeInfo->getStart();
            while ($this->tokens[$seekPosition][1] !== Lexer::TOKEN_HORIZONTAL_WS) {
                --$seekPosition;
            }

            $this->removedNodePositions[] = new StartAndEnd($seekPosition - 1, $removedPhpDocNodeInfo->getEnd());
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
    private function resolveTagSpaceSeparator(PhpDocTagNode $phpDocTagNode): string
    {
        $originalContent = $this->phpDocInfo->getOriginalContent();
        $spacePattern = $this->spacePatternFactory->createSpacePattern($phpDocTagNode);

        $matches = Strings::match($originalContent, $spacePattern);
        if (isset($matches['space'])) {
            return $matches['space'];
        }

        if ($this->isCommonTag($phpDocTagNode)) {
            return ' ';
        }

        return '';
    }

    /**
     * @param AttributeAwareNodeInterface&PhpDocTagNode $phpDocTagNode
     */
    private function hasDescription(PhpDocTagNode $phpDocTagNode): bool
    {
        $hasDescriptionWithOriginalSpaces = $phpDocTagNode->getAttribute(
            Attribute::HAS_DESCRIPTION_WITH_ORIGINAL_SPACES
        );

        if (! $hasDescriptionWithOriginalSpaces) {
            return false;
        }

        if (! property_exists($phpDocTagNode->value, 'description')) {
            return false;
        }

        return (bool) $phpDocTagNode->value->description;
    }

    private function explodeAndImplode(string $content, string $explodeChar, string $implodeChar): string
    {
        $content = explode($explodeChar, $content);

        if (! is_array($content)) {
            throw new ShouldNotHappenException();
        }

        return implode($implodeChar, $content);
    }

    private function isCommonTag(PhpDocTagNode $phpDocTagNode): bool
    {
        if ($phpDocTagNode->value instanceof ParamTagValueNode) {
            return true;
        }

        if ($phpDocTagNode->value instanceof VarTagValueNode) {
            return true;
        }

        if ($phpDocTagNode->value instanceof ReturnTagValueNode) {
            return true;
        }

        return $phpDocTagNode->value instanceof ThrowsTagValueNode;
    }
}
