<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocParser;

use RectorPrefix202312\Nette\Utils\Strings;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use Rector\BetterPhpDocParser\Attributes\AttributeMirrorer;
use Rector\BetterPhpDocParser\Contract\PhpDocParser\PhpDocNodeDecoratorInterface;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\SpacelessPhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\TokenIteratorFactory;
use Rector\BetterPhpDocParser\ValueObject\DoctrineAnnotation\SilentKeyMap;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\BetterPhpDocParser\ValueObject\StartAndEnd;
use Rector\Core\Util\StringUtils;
use RectorPrefix202312\Webmozart\Assert\Assert;
final class DoctrineAnnotationDecorator implements PhpDocNodeDecoratorInterface
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher
     */
    private $classAnnotationMatcher;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser
     */
    private $staticDoctrineAnnotationParser;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\TokenIteratorFactory
     */
    private $tokenIteratorFactory;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\Attributes\AttributeMirrorer
     */
    private $attributeMirrorer;
    /**
     * Special short annotations, that are resolved as FQN by Doctrine annotation parser
     * @var string[]
     */
    private const ALLOWED_SHORT_ANNOTATIONS = ['Target'];
    /**
     * @see https://regex101.com/r/95kIw4/2
     * @var string
     */
    private const LONG_ANNOTATION_REGEX = '#@\\\\(?<class_name>.*?)(?<annotation_content>\\(.*?\\)|,)#';
    /**
     * @see https://regex101.com/r/xWaLOz/1
     * @var string
     */
    private const NESTED_ANNOTATION_END_REGEX = '#(\\s+)?\\}\\)(\\s+)?#';
    public function __construct(\Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher $classAnnotationMatcher, \Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser $staticDoctrineAnnotationParser, TokenIteratorFactory $tokenIteratorFactory, AttributeMirrorer $attributeMirrorer)
    {
        $this->classAnnotationMatcher = $classAnnotationMatcher;
        $this->staticDoctrineAnnotationParser = $staticDoctrineAnnotationParser;
        $this->tokenIteratorFactory = $tokenIteratorFactory;
        $this->attributeMirrorer = $attributeMirrorer;
    }
    public function decorate(PhpDocNode $phpDocNode, Node $phpNode) : void
    {
        // merge split doctrine nested tags
        $this->mergeNestedDoctrineAnnotations($phpDocNode);
        $this->transformGenericTagValueNodesToDoctrineAnnotationTagValueNodes($phpDocNode, $phpNode);
    }
    /**
     * Join token iterator with all the following nodes if nested
     */
    private function mergeNestedDoctrineAnnotations(PhpDocNode $phpDocNode) : void
    {
        $removedKeys = [];
        foreach ($phpDocNode->children as $key => $phpDocChildNode) {
            if (\in_array($key, $removedKeys, \true)) {
                continue;
            }
            if (!$phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }
            if (!$phpDocChildNode->value instanceof GenericTagValueNode) {
                continue;
            }
            $genericTagValueNode = $phpDocChildNode->value;
            while (isset($phpDocNode->children[$key])) {
                ++$key;
                // no more next nodes
                if (!isset($phpDocNode->children[$key])) {
                    break;
                }
                $nextPhpDocChildNode = $phpDocNode->children[$key];
                if ($nextPhpDocChildNode instanceof PhpDocTextNode && StringUtils::isMatch($nextPhpDocChildNode->text, self::NESTED_ANNOTATION_END_REGEX)) {
                    // @todo how to detect previously opened brackets?
                    // probably local property with holding count of opened brackets
                    $composedContent = $genericTagValueNode->value . \PHP_EOL . $nextPhpDocChildNode->text;
                    $genericTagValueNode->value = $composedContent;
                    $startAndEnd = $this->combineStartAndEnd($phpDocChildNode, $nextPhpDocChildNode);
                    $phpDocChildNode->setAttribute(PhpDocAttributeKey::START_AND_END, $startAndEnd);
                    $removedKeys[] = $key;
                    $removedKeys[] = $key + 1;
                    continue;
                }
                if (!$nextPhpDocChildNode instanceof PhpDocTagNode) {
                    continue;
                }
                if (!$nextPhpDocChildNode->value instanceof GenericTagValueNode) {
                    continue;
                }
                if ($this->isClosedContent($genericTagValueNode->value)) {
                    break;
                }
                $composedContent = $genericTagValueNode->value . \PHP_EOL . $nextPhpDocChildNode->name . $nextPhpDocChildNode->value->value;
                // cleanup the next from closing
                $genericTagValueNode->value = $composedContent;
                $startAndEnd = $this->combineStartAndEnd($phpDocChildNode, $nextPhpDocChildNode);
                $phpDocChildNode->setAttribute(PhpDocAttributeKey::START_AND_END, $startAndEnd);
                $currentChildValueNode = $phpDocNode->children[$key];
                if (!$currentChildValueNode instanceof PhpDocTagNode) {
                    continue;
                }
                $currentGenericTagValueNode = $currentChildValueNode->value;
                if (!$currentGenericTagValueNode instanceof GenericTagValueNode) {
                    continue;
                }
                $removedKeys[] = $key;
            }
        }
        foreach (\array_keys($phpDocNode->children) as $key) {
            if (!\in_array($key, $removedKeys, \true)) {
                continue;
            }
            unset($phpDocNode->children[$key]);
        }
    }
    private function processTextSpacelessInTextNode(PhpDocNode $phpDocNode, PhpDocTextNode $phpDocTextNode, Node $currentPhpNode, int $key) : void
    {
        $spacelessPhpDocTagNodes = $this->resolveFqnAnnotationSpacelessPhpDocTagNode($phpDocTextNode, $currentPhpNode);
        if ($spacelessPhpDocTagNodes === []) {
            return;
        }
        $texts = \explode("\n@\\", $phpDocTextNode->text);
        $otherText = $texts[0];
        if (\strncmp($otherText, '@\\', \strlen('@\\')) !== 0 && \trim($otherText) !== '') {
            $phpDocNode->children[$key] = new PhpDocTextNode($otherText);
            \array_splice($phpDocNode->children, $key + 1, 0, $spacelessPhpDocTagNodes);
        } else {
            unset($phpDocNode->children[$key]);
            \array_splice($phpDocNode->children, $key, 0, $spacelessPhpDocTagNodes);
        }
    }
    private function transformGenericTagValueNodesToDoctrineAnnotationTagValueNodes(PhpDocNode $phpDocNode, Node $currentPhpNode) : void
    {
        foreach ($phpDocNode->children as $key => $phpDocChildNode) {
            // the @\FQN use case
            if ($phpDocChildNode instanceof PhpDocTextNode) {
                $this->processTextSpacelessInTextNode($phpDocNode, $phpDocChildNode, $currentPhpNode, $key);
                continue;
            }
            if (!$phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }
            if (!$phpDocChildNode->value instanceof GenericTagValueNode) {
                $this->processDescriptionAsSpacelessPhpDoctagNode($phpDocNode, $phpDocChildNode, $currentPhpNode, $key);
                continue;
            }
            // known doc tag to annotation class
            $fullyQualifiedAnnotationClass = $this->classAnnotationMatcher->resolveTagFullyQualifiedName($phpDocChildNode->name, $currentPhpNode);
            // not an annotations class
            if (\strpos($fullyQualifiedAnnotationClass, '\\') === \false && !\in_array($fullyQualifiedAnnotationClass, self::ALLOWED_SHORT_ANNOTATIONS, \true)) {
                continue;
            }
            while (isset($phpDocNode->children[$key]) && $phpDocNode->children[$key] !== $phpDocChildNode) {
                ++$key;
            }
            $phpDocTextNode = new PhpDocTextNode($phpDocChildNode->value->value);
            $startAndEnd = $phpDocChildNode->value->getAttribute(PhpDocAttributeKey::START_AND_END);
            if (!$startAndEnd instanceof StartAndEnd) {
                $spacelessPhpDocTagNode = $this->createSpacelessPhpDocTagNode($phpDocChildNode->name, $phpDocChildNode->value, $fullyQualifiedAnnotationClass, $currentPhpNode);
                $this->attributeMirrorer->mirror($phpDocChildNode, $spacelessPhpDocTagNode);
                $phpDocNode->children[$key] = $spacelessPhpDocTagNode;
                continue;
            }
            $phpDocTextNode->setAttribute(PhpDocAttributeKey::START_AND_END, $startAndEnd);
            $spacelessPhpDocTagNodes = $this->resolveFqnAnnotationSpacelessPhpDocTagNode($phpDocTextNode, $currentPhpNode);
            if ($spacelessPhpDocTagNodes === []) {
                $spacelessPhpDocTagNode = $this->createSpacelessPhpDocTagNode($phpDocChildNode->name, $phpDocChildNode->value, $fullyQualifiedAnnotationClass, $currentPhpNode);
                $this->attributeMirrorer->mirror($phpDocChildNode, $spacelessPhpDocTagNode);
                $phpDocNode->children[$key] = $spacelessPhpDocTagNode;
                continue;
            }
            Assert::isAOf($phpDocNode->children[$key], PhpDocTagNode::class);
            $texts = \explode("\n@\\", $phpDocChildNode->value->value);
            $phpDocNode->children[$key]->value = new GenericTagValueNode($texts[0]);
            $phpDocNode->children[$key]->value->setAttribute(PhpDocAttributeKey::START_AND_END, $startAndEnd);
            $spacelessPhpDocTagNode = $this->createSpacelessPhpDocTagNode($phpDocNode->children[$key]->name, $phpDocNode->children[$key]->value, $fullyQualifiedAnnotationClass, $currentPhpNode);
            $this->attributeMirrorer->mirror($phpDocNode->children[$key], $spacelessPhpDocTagNode);
            $phpDocNode->children[$key] = $spacelessPhpDocTagNode;
            // require to reprint the generic
            $phpDocNode->children[$key]->value->setAttribute(PhpDocAttributeKey::ORIG_NODE, null);
            \array_splice($phpDocNode->children, $key + 1, 0, $spacelessPhpDocTagNodes);
        }
    }
    private function processDescriptionAsSpacelessPhpDoctagNode(PhpDocNode $phpDocNode, PhpDocTagNode $phpDocTagNode, Node $currentPhpNode, int $key) : void
    {
        if (!\property_exists($phpDocTagNode->value, 'description')) {
            return;
        }
        $description = (string) $phpDocTagNode->value->description;
        if (\strpos($description, "\n") === \false) {
            return;
        }
        $phpDocTextNode = new PhpDocTextNode($description);
        $startAndEnd = $phpDocTagNode->value->getAttribute(PhpDocAttributeKey::START_AND_END);
        if (!$startAndEnd instanceof StartAndEnd) {
            return;
        }
        $phpDocTextNode->setAttribute(PhpDocAttributeKey::START_AND_END, $startAndEnd);
        $spacelessPhpDocTagNodes = $this->resolveFqnAnnotationSpacelessPhpDocTagNode($phpDocTextNode, $currentPhpNode);
        if ($spacelessPhpDocTagNodes === []) {
            return;
        }
        while (isset($phpDocNode->children[$key]) && $phpDocNode->children[$key] !== $phpDocTagNode) {
            ++$key;
        }
        unset($phpDocNode->children[$key]);
        $classNode = new PhpDocTagNode($phpDocTagNode->name, $phpDocTagNode->value);
        $description = Strings::replace($description, self::LONG_ANNOTATION_REGEX, '');
        $description = \substr($description, 0, -7);
        $phpDocTagNode->value->description = $description;
        $phpDocNode->children[$key] = $classNode;
        \array_splice($phpDocNode->children, $key + 1, 0, $spacelessPhpDocTagNodes);
    }
    /**
     * This is closed block, e.g. {( ... )},
     * false on: {( ... )
     */
    private function isClosedContent(string $composedContent) : bool
    {
        $composedTokenIterator = $this->tokenIteratorFactory->create($composedContent);
        $tokenCount = $composedTokenIterator->count();
        $openBracketCount = 0;
        $closeBracketCount = 0;
        if ($composedContent === '') {
            return \true;
        }
        do {
            if ($composedTokenIterator->isCurrentTokenType(Lexer::TOKEN_OPEN_CURLY_BRACKET, Lexer::TOKEN_OPEN_PARENTHESES) || \strpos($composedTokenIterator->currentTokenValue(), '(') !== \false) {
                ++$openBracketCount;
            }
            if ($composedTokenIterator->isCurrentTokenType(Lexer::TOKEN_CLOSE_CURLY_BRACKET, Lexer::TOKEN_CLOSE_PARENTHESES) || \strpos($composedTokenIterator->currentTokenValue(), ')') !== \false) {
                ++$closeBracketCount;
            }
            $composedTokenIterator->next();
        } while ($composedTokenIterator->currentPosition() < $tokenCount - 1);
        return $openBracketCount === $closeBracketCount;
    }
    private function createSpacelessPhpDocTagNode(string $tagName, GenericTagValueNode $genericTagValueNode, string $fullyQualifiedAnnotationClass, Node $currentPhpNode) : SpacelessPhpDocTagNode
    {
        $formerStartEnd = $genericTagValueNode->getAttribute(PhpDocAttributeKey::START_AND_END);
        return $this->createDoctrineSpacelessPhpDocTagNode($genericTagValueNode->value, $tagName, $fullyQualifiedAnnotationClass, $formerStartEnd, $currentPhpNode);
    }
    private function createDoctrineSpacelessPhpDocTagNode(string $annotationContent, string $tagName, string $fullyQualifiedAnnotationClass, StartAndEnd $startAndEnd, Node $currentPhpNode) : SpacelessPhpDocTagNode
    {
        $nestedTokenIterator = $this->tokenIteratorFactory->create($annotationContent);
        // mimics doctrine behavior just in phpdoc-parser syntax :)
        // https://github.com/doctrine/annotations/blob/c66f06b7c83e9a2a7523351a9d5a4b55f885e574/lib/Doctrine/Common/Annotations/DocParser.php#L742
        $values = $this->staticDoctrineAnnotationParser->resolveAnnotationMethodCall($nestedTokenIterator, $currentPhpNode);
        $identifierTypeNode = new IdentifierTypeNode($tagName);
        $identifierTypeNode->setAttribute(PhpDocAttributeKey::RESOLVED_CLASS, $fullyQualifiedAnnotationClass);
        $doctrineAnnotationTagValueNode = new DoctrineAnnotationTagValueNode($identifierTypeNode, $annotationContent, $values, SilentKeyMap::CLASS_NAMES_TO_SILENT_KEYS[$fullyQualifiedAnnotationClass] ?? null);
        $doctrineAnnotationTagValueNode->setAttribute(PhpDocAttributeKey::START_AND_END, $startAndEnd);
        return new SpacelessPhpDocTagNode($tagName, $doctrineAnnotationTagValueNode);
    }
    private function combineStartAndEnd(\PHPStan\PhpDocParser\Ast\Node $startPhpDocChildNode, PhpDocChildNode $endPhpDocChildNode) : StartAndEnd
    {
        /** @var StartAndEnd $currentStartAndEnd */
        $currentStartAndEnd = $startPhpDocChildNode->getAttribute(PhpDocAttributeKey::START_AND_END);
        /** @var StartAndEnd $nextStartAndEnd */
        $nextStartAndEnd = $endPhpDocChildNode->getAttribute(PhpDocAttributeKey::START_AND_END);
        return new StartAndEnd($currentStartAndEnd->getStart(), $nextStartAndEnd->getEnd());
    }
    /**
     * @return SpacelessPhpDocTagNode[]
     */
    private function resolveFqnAnnotationSpacelessPhpDocTagNode(PhpDocTextNode $phpDocTextNode, Node $currentPhpNode) : array
    {
        $matches = Strings::matchAll($phpDocTextNode->text, self::LONG_ANNOTATION_REGEX);
        $spacelessPhpDocTagNodes = [];
        foreach ($matches as $match) {
            $fullyQualifiedAnnotationClass = $match['class_name'] ?? null;
            if ($fullyQualifiedAnnotationClass === null) {
                continue;
            }
            $nestedAnnotationOpen = \explode('(', (string) $fullyQualifiedAnnotationClass);
            $fullyQualifiedAnnotationClass = $nestedAnnotationOpen[0];
            $annotationContent = $match['annotation_content'] ?? null;
            $tagName = '@\\' . $fullyQualifiedAnnotationClass;
            $formerStartEnd = $phpDocTextNode->getAttribute(PhpDocAttributeKey::START_AND_END);
            if (isset($nestedAnnotationOpen[1])) {
                $annotationContent = '("' . \trim($nestedAnnotationOpen[1], '"\'') . '")';
            }
            $spacelessPhpDocTagNodes[] = $this->createDoctrineSpacelessPhpDocTagNode($annotationContent, $tagName, $fullyQualifiedAnnotationClass, $formerStartEnd, $currentPhpNode);
        }
        return $spacelessPhpDocTagNodes;
    }
}
