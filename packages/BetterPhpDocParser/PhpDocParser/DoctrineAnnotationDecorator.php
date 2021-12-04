<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocParser;

use Nette\Utils\Strings;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
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
use Rector\Core\Util\StringUtils;

final class DoctrineAnnotationDecorator
{
    /**
     * Special short annotations, that are resolved as FQN by Doctrine annotation parser
     * @var string[]
     */
    private const ALLOWED_SHORT_ANNOTATIONS = ['Target'];

    /**
     * @see https://regex101.com/r/95kIw4/1
     * @var string
     */
    private const LONG_ANNOTATION_REGEX = '#@\\\\(?<class_name>.*?)(?<annotation_content>\(.*?\))#';

    /**
     * @see https://regex101.com/r/xWaLOz/1
     * @var string
     */
    private const NESTED_ANNOTATION_END_REGEX = '#(\s+)?\}\)(\s+)?#';

    public function __construct(
        private readonly CurrentNodeProvider $currentNodeProvider,
        private readonly ClassAnnotationMatcher $classAnnotationMatcher,
        private readonly StaticDoctrineAnnotationParser $staticDoctrineAnnotationParser,
        private readonly TokenIteratorFactory $tokenIteratorFactory,
        private readonly AttributeMirrorer $attributeMirrorer
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

                if ($nextPhpDocChildNode instanceof PhpDocTextNode && StringUtils::isMatch(
                    $nextPhpDocChildNode->text,
                    self::NESTED_ANNOTATION_END_REGEX
                )) {
                    // @todo how to detect previously opened brackets?
                    // probably local property with holding count of opened brackets
                    $composedContent = $genericTagValueNode->value . PHP_EOL . $nextPhpDocChildNode->text;
                    $genericTagValueNode->value = $composedContent;

                    $startAndEnd = $this->combineStartAndEnd($phpDocChildNode, $nextPhpDocChildNode);
                    $phpDocChildNode->setAttribute(PhpDocAttributeKey::START_AND_END, $startAndEnd);

                    $removedKeys[] = $key;
                    $removedKeys[] = $key + 1;
                    continue;
                }

                if (! $nextPhpDocChildNode instanceof PhpDocTagNode) {
                    continue;
                }

                if (! $nextPhpDocChildNode->value instanceof GenericTagValueNode) {
                    continue;
                }

                if ($this->isClosedContent($genericTagValueNode->value)) {
                    break;
                }

                $composedContent = $genericTagValueNode->value . PHP_EOL . $nextPhpDocChildNode->name . $nextPhpDocChildNode->value->value;

                // cleanup the next from closing
                $genericTagValueNode->value = $composedContent;

                $startAndEnd = $this->combineStartAndEnd($phpDocChildNode, $nextPhpDocChildNode);
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
            // the @\FQN use case
            if ($phpDocChildNode instanceof PhpDocTextNode) {
                $spacelessPhpDocTagNode = $this->resolveFqnAnnotationSpacelessPhpDocTagNode($phpDocChildNode);
                if (! $spacelessPhpDocTagNode instanceof SpacelessPhpDocTagNode) {
                    continue;
                }

                $phpDocNode->children[$key] = $spacelessPhpDocTagNode;
                continue;
            }

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
            if (! \str_contains($fullyQualifiedAnnotationClass, '\\') && ! in_array(
                $fullyQualifiedAnnotationClass,
                self::ALLOWED_SHORT_ANNOTATIONS,
                true
            )) {
                continue;
            }

            $spacelessPhpDocTagNode = $this->createSpacelessPhpDocTagNode(
                $phpDocChildNode->name,
                $phpDocChildNode->value,
                $fullyQualifiedAnnotationClass
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

    private function createSpacelessPhpDocTagNode(
        string $tagName,
        GenericTagValueNode $genericTagValueNode,
        string $fullyQualifiedAnnotationClass
    ): SpacelessPhpDocTagNode {
        $annotationContent = $genericTagValueNode->value;
        $formerStartEnd = $genericTagValueNode->getAttribute(PhpDocAttributeKey::START_AND_END);

        return $this->createDoctrineSpacelessPhpDocTagNode(
            $annotationContent,
            $tagName,
            $fullyQualifiedAnnotationClass,
            $formerStartEnd
        );
    }

    private function createDoctrineSpacelessPhpDocTagNode(
        string $annotationContent,
        string $tagName,
        string $fullyQualifiedAnnotationClass,
        StartAndEnd $startAndEnd
    ): SpacelessPhpDocTagNode {
        $nestedTokenIterator = $this->tokenIteratorFactory->create($annotationContent);

        // mimics doctrine behavior just in phpdoc-parser syntax :)
        // https://github.com/doctrine/annotations/blob/c66f06b7c83e9a2a7523351a9d5a4b55f885e574/lib/Doctrine/Common/Annotations/DocParser.php#L742

        $values = $this->staticDoctrineAnnotationParser->resolveAnnotationMethodCall($nestedTokenIterator);

        $identifierTypeNode = new IdentifierTypeNode($tagName);
        $identifierTypeNode->setAttribute(PhpDocAttributeKey::RESOLVED_CLASS, $fullyQualifiedAnnotationClass);

        $doctrineAnnotationTagValueNode = new DoctrineAnnotationTagValueNode(
            $identifierTypeNode,
            $annotationContent,
            $values,
            SilentKeyMap::CLASS_NAMES_TO_SILENT_KEYS[$fullyQualifiedAnnotationClass] ?? null
        );

        $doctrineAnnotationTagValueNode->setAttribute(PhpDocAttributeKey::START_AND_END, $startAndEnd);

        return new SpacelessPhpDocTagNode($tagName, $doctrineAnnotationTagValueNode);
    }

    private function combineStartAndEnd(
        \PHPStan\PhpDocParser\Ast\Node $startPhpDocChildNode,
        PhpDocChildNode $endPhpDocChildNode
    ): StartAndEnd {
        /** @var StartAndEnd $currentStartAndEnd */
        $currentStartAndEnd = $startPhpDocChildNode->getAttribute(PhpDocAttributeKey::START_AND_END);

        /** @var StartAndEnd $nextStartAndEnd */
        $nextStartAndEnd = $endPhpDocChildNode->getAttribute(PhpDocAttributeKey::START_AND_END);

        return new StartAndEnd($currentStartAndEnd->getStart(), $nextStartAndEnd->getEnd());
    }

    private function resolveFqnAnnotationSpacelessPhpDocTagNode(PhpDocTextNode $phpDocTextNode): ?SpacelessPhpDocTagNode
    {
        $match = Strings::match($phpDocTextNode->text, self::LONG_ANNOTATION_REGEX);
        $fullyQualifiedAnnotationClass = $match['class_name'] ?? null;

        if ($fullyQualifiedAnnotationClass === null) {
            return null;
        }

        $annotationContent = $match['annotation_content'] ?? null;
        $tagName = '@\\' . $fullyQualifiedAnnotationClass;

        $formerStartEnd = $phpDocTextNode->getAttribute(PhpDocAttributeKey::START_AND_END);

        return $this->createDoctrineSpacelessPhpDocTagNode(
            $annotationContent,
            $tagName,
            $fullyQualifiedAnnotationClass,
            $formerStartEnd
        );
    }
}
