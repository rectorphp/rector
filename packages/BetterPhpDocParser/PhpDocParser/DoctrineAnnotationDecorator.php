<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocParser;

use Nette\Utils\Strings;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
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
    /**
     * @see https://regex101.com/r/jHF5D9/1
     * @var string
     */
    private const OPEN_ANNOTATION_SUFFIX_REGEX = '#(\{|\,)$#';

    /**
     * @var CurrentNodeProvider
     */
    private $currentNodeProvider;

    /**
     * @var ClassAnnotationMatcher
     */
    private $classAnnotationMatcher;

    /**
     * @var StaticDoctrineAnnotationParser
     */
    private $staticDoctrineAnnotationParser;

    /**
     * @var TokenIteratorFactory
     */
    private $tokenIteratorFactory;

    /**
     * @var AttributeMirrorer
     */
    private $attributeMirrorer;

    public function __construct(
        CurrentNodeProvider $currentNodeProvider,
        ClassAnnotationMatcher $classAnnotationMatcher,
        StaticDoctrineAnnotationParser $staticDoctrineAnnotationParser,
        TokenIteratorFactory $tokenIteratorFactory,
        AttributeMirrorer $attributeMirrorer
    ) {
        $this->currentNodeProvider = $currentNodeProvider;
        $this->classAnnotationMatcher = $classAnnotationMatcher;
        $this->staticDoctrineAnnotationParser = $staticDoctrineAnnotationParser;
        $this->tokenIteratorFactory = $tokenIteratorFactory;
        $this->attributeMirrorer = $attributeMirrorer;
    }

    public function decorate(PhpDocNode $phpDocNode): void
    {
        $currentPhpNode = $this->currentNodeProvider->getNode();
        if (! $currentPhpNode instanceof Node) {
            throw new ShouldNotHappenException();
        }

        // merge split doctrine nested tags
        $this->mergeNestedDoctrineAnnotations($phpDocNode);

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
            if (! Strings::contains($fullyQualifiedAnnotationClass, '\\')) {
                continue;
            }

            $genericTagValueNode = $phpDocChildNode->value;
            $nestedTokenIterator = $this->tokenIteratorFactory->create($genericTagValueNode->value);

            // mimics doctrine behavior just in phpdoc-parser syntax :)
            // https://github.com/doctrine/annotations/blob/c66f06b7c83e9a2a7523351a9d5a4b55f885e574/lib/Doctrine/Common/Annotations/DocParser.php#L742
            $values = $this->staticDoctrineAnnotationParser->resolveAnnotationMethodCall($nestedTokenIterator);

            $formerStartEnd = $genericTagValueNode->getAttribute(PhpDocAttributeKey::START_AND_END);

            $doctrineAnnotationTagValueNode = new DoctrineAnnotationTagValueNode(
                $fullyQualifiedAnnotationClass,
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

            /** @var GenericTagValueNode $currentGenericTagValueNode */
            $currentGenericTagValueNode = $genericTagValueNode;

            while (Strings::match($currentGenericTagValueNode->value, self::OPEN_ANNOTATION_SUFFIX_REGEX)) {
                $nextPhpDocChildNode = $phpDocNode->children[$key + 1];
                if (! $nextPhpDocChildNode instanceof PhpDocTagNode) {
                    continue;
                }

                if (! $nextPhpDocChildNode->value instanceof GenericTagValueNode) {
                    continue;
                }

                $genericTagValueNode->value .= PHP_EOL . $nextPhpDocChildNode->name . $nextPhpDocChildNode->value;

                /** @var StartAndEnd $currentStartAndEnd */
                $currentStartAndEnd = $phpDocChildNode->getAttribute(PhpDocAttributeKey::START_AND_END);

                /** @var StartAndEnd $nextStartAndEnd */
                $nextStartAndEnd = $nextPhpDocChildNode->getAttribute(PhpDocAttributeKey::START_AND_END);

                $startAndEnd = new StartAndEnd($currentStartAndEnd->getStart(), $nextStartAndEnd->getEnd());
                $phpDocChildNode->setAttribute(PhpDocAttributeKey::START_AND_END, $startAndEnd);

                ++$key;
                if (! isset($phpDocNode->children[$key])) {
                    break;
                }

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
}
