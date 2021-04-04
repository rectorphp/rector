<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Printer;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\Attribute;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\BetterPhpDocParser\ValueObject\StartAndEnd;

/**
 * @see \Rector\Tests\BetterPhpDocParser\PhpDocInfo\PhpDocInfoPrinter\PhpDocInfoPrinterTest
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
     * @see https://regex101.com/r/Jzqzpw/1
     */
    private const MISSING_NEWLINE_REGEX = '#([^\s])\*/$#';

    /**
     * @var string
     * @see https://regex101.com/r/5fJyws/1
     */
    private const CALLABLE_REGEX = '#callable(\s+)\(#';

//    /**
//     * @var string
//     * @see https://regex101.com/r/hFwSMz/1
//     */
//    private const SPACE_AFTER_ASTERISK_REGEX = '#([^*])\*[ \t]+$#sm';

    /**
     * @var string
     */
    private const NEWLINE_WITH_ASTERISK = PHP_EOL . ' * ';

    /**
     * @see https://regex101.com/r/WR3goY/1/
     * @var string
     */
    private const TAG_AND_SPACE_REGEX = '#(@.*?) \(#';

    /**
     * @var PhpDocNode
     */
    private $phpDocNode;

    /**
     * @var PhpDocInfo
     */
    private $phpDocInfo;

    /**
     * @var EmptyPhpDocDetector
     */
    private $emptyPhpDocDetector;

    /**
     * @var DocBlockInliner
     */
    private $docBlockInliner;

    /**
     * @var RemoveTokensPositionResolver
     */
    private $removeTokensPositionResolver;

    /**
     * @var StartAndEnd[]
     */
    private $removedTokensStartsAndEnds = [];

    /**
     * @var BetterTokenIterator
     */
    private $tokenIterator;

    public function __construct(
        EmptyPhpDocDetector $emptyPhpDocDetector,
        DocBlockInliner $docBlockInliner,
        RemoveTokensPositionResolver $removeTokensPositionResolver
    ) {
        $this->emptyPhpDocDetector = $emptyPhpDocDetector;
        $this->docBlockInliner = $docBlockInliner;
        $this->removeTokensPositionResolver = $removeTokensPositionResolver;
    }

    public function printNew(PhpDocInfo $phpDocInfo): string
    {
        $docContent = (string) $phpDocInfo->getPhpDocNode();

        // fix missing newline in the end of docblock - keep BC compatible for both cases until phpstan with phpdoc-parser 0.5.2 is released
        $docContent = Strings::replace($docContent, self::MISSING_NEWLINE_REGEX, "$1\n */");

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
        if ($phpDocInfo->getTokenCount() === 0) {
            // completely new one, just print string version of it
            if ($phpDocInfo->getPhpDocNode()->children === []) {
                return '';
            }

            return (string) $phpDocInfo->getPhpDocNode();
        }

        $this->phpDocNode = $phpDocInfo->getPhpDocNode();
        $this->tokenIterator = $phpDocInfo->getTokenIterator();

        $this->phpDocInfo = $phpDocInfo;

        $phpDocString = $this->printPhpDocNode($this->phpDocNode);

        // hotfix of extra space with callable ()
        return Strings::replace($phpDocString, self::CALLABLE_REGEX, 'callable(');
    }

    private function printPhpDocNode(PhpDocNode $phpDocNode): string
    {
        // no nodes were, so empty doc
        if ($this->emptyPhpDocDetector->isPhpDocNodeEmpty($phpDocNode)) {
            return '';
        }

        $output = '';

        $previousEndTokenPosition = 0;

        // prepare removed node positions
        $this->removedTokensStartsAndEnds = $this->removeTokensPositionResolver->resolve(
            $this->tokenIterator,
            $this->phpDocInfo->getOriginalPhpDocNode(),
            $this->phpDocNode
        );

        $firstTokenPosition = $this->resolveFirstTokenPosition($phpDocNode);

        foreach ($phpDocNode->children as $phpDocChildNode) {
            $startAndEnd = $phpDocChildNode->getAttribute(PhpDocAttributeKey::START_AND_END);

            // include intermezzo tokens
            if ($startAndEnd instanceof StartAndEnd && $previousEndTokenPosition !== 0) {
                $intermezzoContent = $this->addTokensFromTo($previousEndTokenPosition, $startAndEnd->getStart());
                $output .= $intermezzoContent;
            }

            $childNodeContent = $this->printDocChildNode($phpDocChildNode);
            $output .= $childNodeContent;
            if ($startAndEnd instanceof StartAndEnd) {
                $previousEndTokenPosition = $startAndEnd->getEnd();
            }
        }

        $startContent = $this->addTokensFromTo(0, $firstTokenPosition);
        $endContent = $this->addTokensFromTo($previousEndTokenPosition, $this->tokenIterator->count());

        return $startContent . $output . $endContent;
    }

    private function printDocChildNode(PhpDocChildNode $phpDocChildNode): string
    {
        if ($phpDocChildNode instanceof PhpDocTagNode) {
            if ($phpDocChildNode->value instanceof ParamTagValueNode || $phpDocChildNode->value instanceof ThrowsTagValueNode || $phpDocChildNode->value instanceof VarTagValueNode || $phpDocChildNode->value instanceof ReturnTagValueNode || $phpDocChildNode->value instanceof PropertyTagValueNode) {
                $typeNode = $phpDocChildNode->value->type;
                $typeStartAndEnd = $typeNode->getAttribute(Attribute::START_END);

                // the type has changed → reprint
                if ($typeStartAndEnd === null) {
                    if ($this->phpDocInfo->isSingleLine()) {
                        return ' ' . $phpDocChildNode;
                    }

                    return self::NEWLINE_WITH_ASTERISK . $phpDocChildNode;
                }
            }

            if ($phpDocChildNode->value instanceof DoctrineAnnotationTagValueNode) {
                $valueStartAndEnd = $phpDocChildNode->value->getAttribute(Attribute::START_END);
                if ($valueStartAndEnd === null) {
                    $printedNode = (string) $phpDocChildNode;

                    // remove extra space between tags
                    $printedNode = Strings::replace($printedNode, self::TAG_AND_SPACE_REGEX, '$1(');
                    return self::NEWLINE_WITH_ASTERISK . $printedNode;
                }
            }
        }

        /** @var StartAndEnd|null $startAndEnd */
        $startAndEnd = $phpDocChildNode->getAttribute(Attribute::START_END);

        $shouldReprint = false;
        if ($phpDocChildNode instanceof PhpDocTagNode) {
            $phpDocTagValueNodeStartAndEnd = $phpDocChildNode->value->getAttribute(PhpDocAttributeKey::START_AND_END);
            if (! $phpDocTagValueNodeStartAndEnd instanceof StartAndEnd) {
                $shouldReprint = true;
            }
        }

        if ($startAndEnd instanceof StartAndEnd && ! $shouldReprint) {
            return $this->addTokensFromTo($startAndEnd->getStart(), $startAndEnd->getEnd());
        }

        if ($this->phpDocInfo->isSingleLine()) {
            return ' ' . $phpDocChildNode;
        }

        return self::NEWLINE_WITH_ASTERISK . $phpDocChildNode;
    }

    private function addTokensFromTo(int $from, int $to): string
    {
        $tokens = $this->tokenIterator->getTokens();

        $output = '';
        for ($i = $from; $i < $to; ++$i) {
            foreach ($this->removedTokensStartsAndEnds as $removedTokenStartAndEnd) {
                $removedTokenStartAndEndContains = $removedTokenStartAndEnd->contains($i);
                if ($removedTokenStartAndEndContains) {
                    continue 2;
                }
            }

            $output .= $tokens[$i][0] ?? '';
        }

        return $output;
    }

    private function resolveFirstTokenPosition(PhpDocNode $phpDocNode): int
    {
        foreach ($phpDocNode->children as $phpDocChildNode) {
            $startAndEnd = $phpDocChildNode->getAttribute(PhpDocAttributeKey::START_AND_END);
            if (! $startAndEnd instanceof StartAndEnd) {
                continue;
            }

            return $startAndEnd->getStart();
        }

        return 0;
    }
}
