<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocParser;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use Rector\BetterPhpDocParser\Attributes\AttributeMirrorer;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\SpacelessPhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\TokenIteratorFactory;
use Rector\BetterPhpDocParser\ValueObject\DoctrineAnnotation\SilentKeyMap;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\BetterPhpDocParser\ValueObject\StartAndEnd;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\Core\Exception\ShouldNotHappenException;

final class DoctrineAnnotationDecorator
{
    public function __construct(
        private CurrentNodeProvider $currentNodeProvider,
        private ClassAnnotationMatcher $classAnnotationMatcher,
        private StaticDoctrineAnnotationParser $staticDoctrineAnnotationParser,
        private TokenIteratorFactory $tokenIteratorFactory,
        private AttributeMirrorer $attributeMirrorer
    ) {
    }

    public function decorate(PhpDocNode $phpDocNode): void
    {
        $currentPhpNode = $this->currentNodeProvider->getNode();
        if (! $currentPhpNode instanceof Node) {
            throw new ShouldNotHappenException();
        }

        // merge split doctrine nested tags
        $this->mergeNestedDoctrineAnnotations($phpDocNode);

        $this->transformGenericTagValueNodesToDoctrineAnnotationTagValueNodes($phpDocNode, $currentPhpNode);
    }

    /**
     * Join token iterator with all the following nodes if nested
     */
    private function mergeNestedDoctrineAnnotations(PhpDocNode $phpDocNode): void
    {
        $removedKeys = [];

        foreach ($phpDocNode->children as $key => $phpDocChildNode) {
            if (in_array($key, $removedKeys, true)) {
                continue;
            }

            if (! $phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }

            if (! $phpDocChildNode->value instanceof GenericTagValueNode) {
                continue;
            }

            $genericTagValueNode = $phpDocChildNode->value;

            while (isset($phpDocNode->children[$key])) {
                ++$key;

                // no more next nodes
                if (! isset($phpDocNode->children[$key])) {
                    break;
                }

                $nextPhpDocChildNode = $phpDocNode->children[$key];
                if (! $nextPhpDocChildNode instanceof PhpDocTagNode) {
                    continue;
                }

                if (! $nextPhpDocChildNode->value instanceof GenericTagValueNode) {
                    continue;
                }

                if ($this->isClosedContent($genericTagValueNode->value)) {
                    break;
                }

                $composedContent = $genericTagValueNode->value . PHP_EOL . $nextPhpDocChildNode->name . $nextPhpDocChildNode->value;
                $genericTagValueNode->value = $composedContent;

                /** @var StartAndEnd $currentStartAndEnd */
                $currentStartAndEnd = $phpDocChildNode->getAttribute(PhpDocAttributeKey::START_AND_END);

                /** @var StartAndEnd $nextStartAndEnd */
                $nextStartAndEnd = $nextPhpDocChildNode->getAttribute(PhpDocAttributeKey::START_AND_END);

                $startAndEnd = new StartAndEnd($currentStartAndEnd->getStart(), $nextStartAndEnd->getEnd());
                $phpDocChildNode->setAttribute(PhpDocAttributeKey::START_AND_END, $startAndEnd);

                $currentChildValueNode = $phpDocNode->children[$key];
                if (! $currentChildValueNode instanceof PhpDocTagNode) {
                    continue;
                }

                $currentGenericTagValueNode = $currentChildValueNode->value;
                if (! $currentGenericTagValueNode instanceof GenericTagValueNode) {
                    continue;
                }

                $removedKeys[] = $key;
            }
        }

        foreach (array_keys($phpDocNode->children) as $key) {
            if (! in_array($key, $removedKeys, true)) {
                continue;
            }

            unset($phpDocNode->children[$key]);
        }
    }

    private function transformGenericTagValueNodesToDoctrineAnnotationTagValueNodes(
        PhpDocNode $phpDocNode,
        Node $currentPhpNode
    ): void {
        foreach ($phpDocNode->children as $key => $phpDocChildNode) {
            if (! $phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }

            if (! $phpDocChildNode->value instanceof GenericTagValueNode) {
                continue;
            }

            // known doc tag to annotation class
            $fullyQualifiedAnnotationClass = $this->classAnnotationMatcher->resolveTagFullyQualifiedName(
                $phpDocChildNode->name,
                $currentPhpNode
            );

            // not an annotations class
            if (! \str_contains($fullyQualifiedAnnotationClass, '\\')) {
                continue;
            }

            $genericTagValueNode = $phpDocChildNode->value;
            $nestedTokenIterator = $this->tokenIteratorFactory->create($genericTagValueNode->value);

            // mimics doctrine behavior just in phpdoc-parser syntax :)
            // https://github.com/doctrine/annotations/blob/c66f06b7c83e9a2a7523351a9d5a4b55f885e574/lib/Doctrine/Common/Annotations/DocParser.php#L742
            $values = $this->staticDoctrineAnnotationParser->resolveAnnotationMethodCall($nestedTokenIterator);

            $formerStartEnd = $genericTagValueNode->getAttribute(PhpDocAttributeKey::START_AND_END);

            $identifierTypeNode = new IdentifierTypeNode($phpDocChildNode->name);
            $identifierTypeNode->setAttribute(PhpDocAttributeKey::RESOLVED_CLASS, $fullyQualifiedAnnotationClass);

            $doctrineAnnotationTagValueNode = new DoctrineAnnotationTagValueNode(
                $identifierTypeNode,
                $genericTagValueNode->value,
                $values,
                SilentKeyMap::CLASS_NAMES_TO_SILENT_KEYS[$fullyQualifiedAnnotationClass] ?? null
            );

            $doctrineAnnotationTagValueNode->setAttribute(PhpDocAttributeKey::START_AND_END, $formerStartEnd);

            $spacelessPhpDocTagNode = new SpacelessPhpDocTagNode(
                $phpDocChildNode->name,
                $doctrineAnnotationTagValueNode
            );

            $this->attributeMirrorer->mirror($phpDocChildNode, $spacelessPhpDocTagNode);
            $phpDocNode->children[$key] = $spacelessPhpDocTagNode;
        }
    }

    /**
     * This is closed block, e.g. {( ... )},
     * false on: {( ... )
     */
    private function isClosedContent(string $composedContent): bool
    {
        $composedTokenIterator = $this->tokenIteratorFactory->create($composedContent);
        $tokenCount = $composedTokenIterator->count();

        $openBracketCount = 0;
        $closeBracketCount = 0;
        if ($composedContent === '') {
            return true;
        }

        do {
            if ($composedTokenIterator->isCurrentTokenTypes([
                Lexer::TOKEN_OPEN_CURLY_BRACKET,
                Lexer::TOKEN_OPEN_PARENTHESES,
            ]) || \str_contains($composedTokenIterator->currentTokenValue(), '(')) {
                ++$openBracketCount;
            }

            if ($composedTokenIterator->isCurrentTokenTypes([
                Lexer::TOKEN_CLOSE_CURLY_BRACKET,
                Lexer::TOKEN_CLOSE_PARENTHESES,
                // sometimes it gets mixed int    ")
            ]) || \str_contains($composedTokenIterator->currentTokenValue(), ')')) {
                ++$closeBracketCount;
            }

            $composedTokenIterator->next();
        } while ($composedTokenIterator->currentPosition() < ($tokenCount - 1));

        return $openBracketCount === $closeBracketCount;
    }
}
