<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocInfo;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\InvalidTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\Annotation\AnnotationNaming;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\SpacelessPhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocNodeFinder\PhpDocNodeByTypeFinder;
use Rector\BetterPhpDocParser\PhpDocNodeVisitor\ChangedPhpDocNodeVisitor;
use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use Rector\BetterPhpDocParser\ValueObject\Type\ShortenedIdentifierTypeNode;
use Rector\ChangesReporting\Collector\RectorChangeCollector;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\StaticTypeMapper\StaticTypeMapper;
use RectorPrefix20220531\Symplify\Astral\PhpDocParser\PhpDocNodeTraverser;
/**
 * @template TNode as \PHPStan\PhpDocParser\Ast\Node
 * @see \Rector\Tests\BetterPhpDocParser\PhpDocInfo\PhpDocInfo\PhpDocInfoTest
 */
final class PhpDocInfo
{
    /**
     * @var array<class-string<PhpDocTagValueNode>, string>
     */
    private const TAGS_TYPES_TO_NAMES = [\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode::class => '@return', \PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode::class => '@param', \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode::class => '@var', \PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode::class => '@method', \PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode::class => '@property'];
    /**
     * @var bool
     */
    private $isSingleLine = \false;
    /**
     * @readonly
     * @var \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode
     */
    private $originalPhpDocNode;
    /**
     * @var bool
     */
    private $hasChanged = \false;
    /**
     * @readonly
     * @var \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode
     */
    private $phpDocNode;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator
     */
    private $betterTokenIterator;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \PhpParser\Node
     */
    private $node;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\Annotation\AnnotationNaming
     */
    private $annotationNaming;
    /**
     * @readonly
     * @var \Rector\Core\Configuration\CurrentNodeProvider
     */
    private $currentNodeProvider;
    /**
     * @readonly
     * @var \Rector\ChangesReporting\Collector\RectorChangeCollector
     */
    private $rectorChangeCollector;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocNodeFinder\PhpDocNodeByTypeFinder
     */
    private $phpDocNodeByTypeFinder;
    public function __construct(\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode $phpDocNode, \Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator $betterTokenIterator, \Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \PhpParser\Node $node, \Rector\BetterPhpDocParser\Annotation\AnnotationNaming $annotationNaming, \Rector\Core\Configuration\CurrentNodeProvider $currentNodeProvider, \Rector\ChangesReporting\Collector\RectorChangeCollector $rectorChangeCollector, \Rector\BetterPhpDocParser\PhpDocNodeFinder\PhpDocNodeByTypeFinder $phpDocNodeByTypeFinder)
    {
        $this->phpDocNode = $phpDocNode;
        $this->betterTokenIterator = $betterTokenIterator;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->node = $node;
        $this->annotationNaming = $annotationNaming;
        $this->currentNodeProvider = $currentNodeProvider;
        $this->rectorChangeCollector = $rectorChangeCollector;
        $this->phpDocNodeByTypeFinder = $phpDocNodeByTypeFinder;
        $this->originalPhpDocNode = clone $phpDocNode;
        if (!$betterTokenIterator->containsTokenType(\PHPStan\PhpDocParser\Lexer\Lexer::TOKEN_PHPDOC_EOL)) {
            $this->isSingleLine = \true;
        }
    }
    public function addPhpDocTagNode(\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode $phpDocChildNode) : void
    {
        $this->phpDocNode->children[] = $phpDocChildNode;
        // to give node more space
        $this->makeMultiLined();
        $this->markAsChanged();
    }
    public function getPhpDocNode() : \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode
    {
        return $this->phpDocNode;
    }
    public function getOriginalPhpDocNode() : \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode
    {
        return $this->originalPhpDocNode;
    }
    /**
     * @return mixed[]
     */
    public function getTokens() : array
    {
        return $this->betterTokenIterator->getTokens();
    }
    public function getTokenCount() : int
    {
        return $this->betterTokenIterator->count();
    }
    public function getVarTagValueNode(string $tagName = '@var') : ?\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode
    {
        return $this->phpDocNode->getVarTagValues($tagName)[0] ?? null;
    }
    /**
     * @return array<PhpDocTagNode>
     */
    public function getTagsByName(string $name) : array
    {
        // for simple tag names only
        if (\strpos($name, '\\') !== \false) {
            return [];
        }
        $tags = $this->phpDocNode->getTags();
        $name = $this->annotationNaming->normalizeName($name);
        $tags = \array_filter($tags, function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) use($name) : bool {
            return $tag->name === $name;
        });
        $tags = \array_values($tags);
        return \array_values($tags);
    }
    public function getParamType(string $name) : \PHPStan\Type\Type
    {
        $paramTagValueNodes = $this->getParamTagValueByName($name);
        return $this->getTypeOrMixed($paramTagValueNodes);
    }
    /**
     * @return ParamTagValueNode[]
     */
    public function getParamTagValueNodes() : array
    {
        return $this->phpDocNode->getParamTagValues();
    }
    public function getParamTagValueNodeByName(string $parameterName) : ?\PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode
    {
        foreach ($this->phpDocNode->getParamTagValues() as $paramTagValueNode) {
            if ($paramTagValueNode->parameterName !== '$' . $parameterName) {
                continue;
            }
            return $paramTagValueNode;
        }
        return null;
    }
    public function getVarType(string $tagName = '@var') : \PHPStan\Type\Type
    {
        return $this->getTypeOrMixed($this->getVarTagValueNode($tagName));
    }
    public function getReturnType() : \PHPStan\Type\Type
    {
        return $this->getTypeOrMixed($this->getReturnTagValue());
    }
    /**
     * @param class-string<TNode> $type
     * @return TNode[]
     */
    public function getByType(string $type) : array
    {
        return $this->phpDocNodeByTypeFinder->findByType($this->phpDocNode, $type);
    }
    /**
     * @param class-string<TNode> $type
     */
    public function hasByType(string $type) : bool
    {
        return $this->phpDocNodeByTypeFinder->findByType($this->phpDocNode, $type) !== [];
    }
    /**
     * @param array<class-string<TNode>> $types
     */
    public function hasByTypes(array $types) : bool
    {
        foreach ($types as $type) {
            if ($this->hasByType($type)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param string[] $names
     */
    public function hasByNames(array $names) : bool
    {
        foreach ($names as $name) {
            if ($this->hasByName($name)) {
                return \true;
            }
        }
        return \false;
    }
    public function hasByName(string $name) : bool
    {
        return (bool) $this->getTagsByName($name);
    }
    public function getByName(string $name) : ?\PHPStan\PhpDocParser\Ast\Node
    {
        return $this->getTagsByName($name)[0] ?? null;
    }
    /**
     * @param class-string[] $classes
     */
    public function getByAnnotationClasses(array $classes) : ?\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode
    {
        $doctrineAnnotationTagValueNodes = $this->phpDocNodeByTypeFinder->findDoctrineAnnotationsByClasses($this->phpDocNode, $classes);
        return $doctrineAnnotationTagValueNodes[0] ?? null;
    }
    /**
     * @param class-string $class
     */
    public function getByAnnotationClass(string $class) : ?\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode
    {
        $doctrineAnnotationTagValueNodes = $this->phpDocNodeByTypeFinder->findDoctrineAnnotationsByClass($this->phpDocNode, $class);
        return $doctrineAnnotationTagValueNodes[0] ?? null;
    }
    /**
     * @param class-string $class
     */
    public function hasByAnnotationClass(string $class) : bool
    {
        return $this->findByAnnotationClass($class) !== [];
    }
    /**
     * @param string[] $annotationsClasses
     */
    public function hasByAnnotationClasses(array $annotationsClasses) : bool
    {
        return $this->getByAnnotationClasses($annotationsClasses) !== null;
    }
    /**
     * @param string[] $desiredClasses
     */
    public function findOneByAnnotationClasses(array $desiredClasses) : ?\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode
    {
        foreach ($desiredClasses as $desiredClass) {
            $doctrineAnnotationTagValueNode = $this->findOneByAnnotationClass($desiredClass);
            if (!$doctrineAnnotationTagValueNode instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
                continue;
            }
            return $doctrineAnnotationTagValueNode;
        }
        return null;
    }
    /**
     * @param class-string $desiredClass
     */
    public function findOneByAnnotationClass(string $desiredClass) : ?\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode
    {
        $foundTagValueNodes = $this->findByAnnotationClass($desiredClass);
        return $foundTagValueNodes[0] ?? null;
    }
    /**
     * @param class-string $desiredClass
     * @return DoctrineAnnotationTagValueNode[]
     */
    public function findByAnnotationClass(string $desiredClass) : array
    {
        return $this->phpDocNodeByTypeFinder->findDoctrineAnnotationsByClass($this->phpDocNode, $desiredClass);
    }
    /**
     * @template T of \PHPStan\PhpDocParser\Ast\Node
     * @param class-string<T> $typeToRemove
     */
    public function removeByType(string $typeToRemove) : void
    {
        $phpDocNodeTraverser = new \RectorPrefix20220531\Symplify\Astral\PhpDocParser\PhpDocNodeTraverser();
        $phpDocNodeTraverser->traverseWithCallable($this->phpDocNode, '', function (\PHPStan\PhpDocParser\Ast\Node $node) use($typeToRemove) : ?int {
            if ($node instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode && \is_a($node->value, $typeToRemove, \true)) {
                // keep special annotation for tools
                if (\strncmp($node->name, '@psalm-', \strlen('@psalm-')) === 0) {
                    return null;
                }
                if (\strncmp($node->name, '@phpstan-', \strlen('@phpstan-')) === 0) {
                    return null;
                }
                $this->markAsChanged();
                return \RectorPrefix20220531\Symplify\Astral\PhpDocParser\PhpDocNodeTraverser::NODE_REMOVE;
            }
            if (!\is_a($node, $typeToRemove, \true)) {
                return null;
            }
            $this->markAsChanged();
            return \RectorPrefix20220531\Symplify\Astral\PhpDocParser\PhpDocNodeTraverser::NODE_REMOVE;
        });
    }
    /**
     * @return array<string, Type>
     */
    public function getParamTypesByName() : array
    {
        $paramTypesByName = [];
        foreach ($this->phpDocNode->getParamTagValues() as $paramTagValueNode) {
            $parameterName = $paramTagValueNode->parameterName;
            $parameterType = $this->staticTypeMapper->mapPHPStanPhpDocTypeToPHPStanType($paramTagValueNode, $this->node);
            $paramTypesByName[$parameterName] = $parameterType;
        }
        return $paramTypesByName;
    }
    public function addTagValueNode(\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $phpDocTagValueNode) : void
    {
        if ($phpDocTagValueNode instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
            if ($phpDocTagValueNode->identifierTypeNode instanceof \Rector\BetterPhpDocParser\ValueObject\Type\ShortenedIdentifierTypeNode) {
                $name = '@' . $phpDocTagValueNode->identifierTypeNode;
            } else {
                $name = '@\\' . $phpDocTagValueNode->identifierTypeNode;
            }
            $spacelessPhpDocTagNode = new \Rector\BetterPhpDocParser\PhpDoc\SpacelessPhpDocTagNode($name, $phpDocTagValueNode);
            $this->addPhpDocTagNode($spacelessPhpDocTagNode);
            return;
        }
        $name = $this->resolveNameForPhpDocTagValueNode($phpDocTagValueNode);
        if (!\is_string($name)) {
            return;
        }
        $phpDocTagNode = new \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode($name, $phpDocTagValueNode);
        $this->addPhpDocTagNode($phpDocTagNode);
    }
    public function isNewNode() : bool
    {
        if ($this->phpDocNode->children === []) {
            return \false;
        }
        return $this->betterTokenIterator->count() === 0;
    }
    public function makeSingleLined() : void
    {
        $this->isSingleLine = \true;
    }
    public function isSingleLine() : bool
    {
        return $this->isSingleLine;
    }
    public function hasInvalidTag(string $name) : bool
    {
        // fallback for invalid tag value node
        foreach ($this->phpDocNode->children as $phpDocChildNode) {
            if (!$phpDocChildNode instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode) {
                continue;
            }
            if ($phpDocChildNode->name !== $name) {
                continue;
            }
            if (!$phpDocChildNode->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\InvalidTagValueNode) {
                continue;
            }
            return \true;
        }
        return \false;
    }
    public function getReturnTagValue() : ?\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode
    {
        $returnTagValueNodes = $this->phpDocNode->getReturnTagValues();
        return $returnTagValueNodes[0] ?? null;
    }
    public function getParamTagValueByName(string $name) : ?\PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode
    {
        $desiredParamNameWithDollar = '$' . \ltrim($name, '$');
        foreach ($this->getParamTagValueNodes() as $paramTagValueNode) {
            if ($paramTagValueNode->parameterName !== $desiredParamNameWithDollar) {
                continue;
            }
            return $paramTagValueNode;
        }
        return null;
    }
    /**
     * @return TemplateTagValueNode[]
     */
    public function getTemplateTagValueNodes() : array
    {
        return $this->phpDocNode->getTemplateTagValues();
    }
    public function hasInheritDoc() : bool
    {
        if ($this->hasByNames(['inheritdoc', 'inheritDoc'])) {
            return \true;
        }
        foreach ($this->phpDocNode->children as $children) {
            if (!$children instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode) {
                continue;
            }
            if (\in_array($children->text, ['{@inheritdoc}', '{@inheritDoc}'], \true)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @deprecated
     * Should be handled by attributes of phpdoc node - if stard_and_end is missing in one of nodes, it has been changed
     * Similar to missing original node in php-aprser
     */
    public function markAsChanged() : void
    {
        $this->hasChanged = \true;
        $node = $this->currentNodeProvider->getNode();
        if ($node !== null) {
            $this->rectorChangeCollector->notifyNodeFileInfo($node);
        }
    }
    public function hasChanged() : bool
    {
        if ($this->isNewNode()) {
            return \true;
        }
        if ($this->hasChanged) {
            return \true;
        }
        // has a single node with missing start_end
        $phpDocNodeTraverser = new \RectorPrefix20220531\Symplify\Astral\PhpDocParser\PhpDocNodeTraverser();
        $changedPhpDocNodeVisitor = new \Rector\BetterPhpDocParser\PhpDocNodeVisitor\ChangedPhpDocNodeVisitor();
        $phpDocNodeTraverser->addPhpDocNodeVisitor($changedPhpDocNodeVisitor);
        $phpDocNodeTraverser->traverse($this->phpDocNode);
        return $changedPhpDocNodeVisitor->hasChanged();
    }
    /**
     * @return string[]
     */
    public function getMethodTagNames() : array
    {
        $methodTagNames = [];
        foreach ($this->phpDocNode->getMethodTagValues() as $methodTagValueNode) {
            $methodTagNames[] = $methodTagValueNode->methodName;
        }
        return $methodTagNames;
    }
    public function makeMultiLined() : void
    {
        $this->isSingleLine = \false;
    }
    public function getNode() : \PhpParser\Node
    {
        return $this->node;
    }
    public function resolveNameForPhpDocTagValueNode(\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $phpDocTagValueNode) : ?string
    {
        foreach (self::TAGS_TYPES_TO_NAMES as $tagValueNodeType => $name) {
            /** @var class-string<PhpDocTagNode> $tagValueNodeType */
            if (\is_a($phpDocTagValueNode, $tagValueNodeType, \true)) {
                return $name;
            }
        }
        return null;
    }
    /**
     * @return \PHPStan\Type\MixedType|\PHPStan\Type\Type
     */
    private function getTypeOrMixed(?\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $phpDocTagValueNode)
    {
        if ($phpDocTagValueNode === null) {
            return new \PHPStan\Type\MixedType();
        }
        return $this->staticTypeMapper->mapPHPStanPhpDocTypeToPHPStanType($phpDocTagValueNode, $this->node);
    }
}
