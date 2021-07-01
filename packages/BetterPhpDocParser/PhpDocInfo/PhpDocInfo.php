<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocInfo;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\InvalidTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\Annotation\AnnotationNaming;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\SpacelessPhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocNodeFinder\DoctrineAnnotationMatcher;
use Rector\BetterPhpDocParser\PhpDocNodeVisitor\ChangedPhpDocNodeVisitor;
use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
use Rector\ChangesReporting\Collector\RectorChangeCollector;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\SimplePhpDocParser\PhpDocNodeTraverser;

/**
 * @template TNode as \PHPStan\PhpDocParser\Ast\Node
 * @see \Rector\Tests\BetterPhpDocParser\PhpDocInfo\PhpDocInfo\PhpDocInfoTest
 */
final class PhpDocInfo
{
    /**
     * @var array<class-string<PhpDocTagValueNode>, string>
     */
    private const TAGS_TYPES_TO_NAMES = [
        ReturnTagValueNode::class => '@return',
        ParamTagValueNode::class => '@param',
        VarTagValueNode::class => '@var',
        MethodTagValueNode::class => '@method',
        PropertyTagValueNode::class => '@property',
    ];

    private bool $isSingleLine = false;

    private PhpDocNode $originalPhpDocNode;

    private bool $hasChanged = false;

    public function __construct(
        private PhpDocNode $phpDocNode,
        private BetterTokenIterator $betterTokenIterator,
        private StaticTypeMapper $staticTypeMapper,
        private \PhpParser\Node $node,
        private AnnotationNaming $annotationNaming,
        private CurrentNodeProvider $currentNodeProvider,
        private RectorChangeCollector $rectorChangeCollector,
        private DoctrineAnnotationMatcher $doctrineAnnotationMatcher
    ) {
        $this->originalPhpDocNode = clone $phpDocNode;

        if (! $betterTokenIterator->containsTokenType(Lexer::TOKEN_PHPDOC_EOL)) {
            $this->isSingleLine = true;
        }
    }

    public function addPhpDocTagNode(PhpDocChildNode $phpDocChildNode): void
    {
        $this->phpDocNode->children[] = $phpDocChildNode;
        // to give node more space
        $this->makeMultiLined();

        $this->markAsChanged();
    }

    public function getPhpDocNode(): PhpDocNode
    {
        return $this->phpDocNode;
    }

    public function getOriginalPhpDocNode(): PhpDocNode
    {
        return $this->originalPhpDocNode;
    }

    /**
     * @return mixed[]
     */
    public function getTokens(): array
    {
        return $this->betterTokenIterator->getTokens();
    }

    public function getTokenCount(): int
    {
        return $this->betterTokenIterator->count();
    }

    public function getVarTagValueNode(string $tagName = '@var'): ?VarTagValueNode
    {
        return $this->phpDocNode->getVarTagValues($tagName)[0] ?? null;
    }

    /**
     * @return array<PhpDocTagNode>
     */
    public function getAllTags(): array
    {
        return $this->phpDocNode->getTags();
    }

    /**
     * @return array<PhpDocTagNode>
     */
    public function getTagsByName(string $name): array
    {
        // for simple tag names only
        if (str_contains($name, '\\')) {
            return [];
        }

        $tags = $this->phpDocNode->getTags();
        $name = $this->annotationNaming->normalizeName($name);

        $tags = array_filter($tags, fn (PhpDocTagNode $tag) => $tag->name === $name);

        $tags = array_values($tags);
        return array_values($tags);
    }

    public function getParamType(string $name): Type
    {
        $paramTagValueNodes = $this->getParamTagValueByName($name);

        return $this->getTypeOrMixed($paramTagValueNodes);
    }

    /**
     * @return ParamTagValueNode[]
     */
    public function getParamTagValueNodes(): array
    {
        return $this->phpDocNode->getParamTagValues();
    }

    public function getParamTagValueNodeByName(string $parameterName): ?ParamTagValueNode
    {
        foreach ($this->phpDocNode->getParamTagValues() as $paramTagValueNode) {
            if ($paramTagValueNode->parameterName !== '$' . $parameterName) {
                continue;
            }

            return $paramTagValueNode;
        }

        return null;
    }

    public function getVarType(string $tagName = '@var'): Type
    {
        return $this->getTypeOrMixed($this->getVarTagValueNode($tagName));
    }

    public function getReturnType(): Type
    {
        return $this->getTypeOrMixed($this->getReturnTagValue());
    }

    /**
     * @param class-string<TNode> $type
     */
    public function hasByType(string $type): bool
    {
        return (bool) $this->getByType($type);
    }

    /**
     * @param array<class-string<TNode>> $types
     */
    public function hasByTypes(array $types): bool
    {
        foreach ($types as $type) {
            if ($this->hasByType($type)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string[] $names
     */
    public function hasByNames(array $names): bool
    {
        foreach ($names as $name) {
            if ($this->hasByName($name)) {
                return true;
            }
        }

        return false;
    }

    public function hasByName(string $name): bool
    {
        return (bool) $this->getTagsByName($name);
    }

    public function getByName(string $name): ?Node
    {
        return $this->getTagsByName($name)[0] ?? null;
    }

    /**
     * @param class-string[] $classes
     */
    public function getByAnnotationClasses(array $classes): ?DoctrineAnnotationTagValueNode
    {
        foreach ($classes as $class) {
            $tagValueNode = $this->findOneByAnnotationClass($class);
            if ($tagValueNode instanceof DoctrineAnnotationTagValueNode) {
                return $tagValueNode;
            }
        }

        return null;
    }

    /**
     * @param class-string $class
     */
    public function getByAnnotationClass(string $class): ?DoctrineAnnotationTagValueNode
    {
        return $this->findOneByAnnotationClass($class);
    }

    public function hasByAnnotationClass(string $class): bool
    {
        return $this->findByAnnotationClass($class) !== [];
    }

    /**
     * @param string[] $annotationsClasses
     */
    public function hasByAnnotationClasses(array $annotationsClasses): bool
    {
        return $this->getByAnnotationClasses($annotationsClasses) !== null;
    }

    /**
     * @param class-string $desiredClass
     */
    public function findOneByAnnotationClass(string $desiredClass): ?DoctrineAnnotationTagValueNode
    {
        $foundTagValueNodes = $this->findByAnnotationClass($desiredClass);
        return $foundTagValueNodes[0] ?? null;
    }

    /**
     * @param class-string $desiredClass
     * @return DoctrineAnnotationTagValueNode[]
     */
    public function findByAnnotationClass(string $desiredClass): array
    {
        return $this->filterDoctrineTagValuesNodesINcludingNested($desiredClass);
    }

    /**
     * @param class-string<TNode> $type
     * @return TNode|null
     */
    public function getByType(string $type)
    {
        foreach ($this->phpDocNode->children as $phpDocChildNode) {
            if (is_a($phpDocChildNode, $type, true)) {
                return $phpDocChildNode;
            }

            if (! $phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }

            if (! is_a($phpDocChildNode->value, $type, true)) {
                continue;
            }

            return $phpDocChildNode->value;
        }

        return null;
    }

    /**
     * @deprecated, should accept only strings, to make it useful for developer who don't know internal logics of tag nodes; also not each tag requires node class
     * @template T of \PHPStan\PhpDocParser\Ast\Node
     * @param class-string<T> $type
     */
    public function removeByType(string $type): void
    {
        foreach ($this->phpDocNode->children as $key => $phpDocChildNode) {
            if (is_a($phpDocChildNode, $type, true)) {
                unset($this->phpDocNode->children[$key]);
                $this->markAsChanged();
            }

            if (! $phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }

            if (! is_a($phpDocChildNode->value, $type, true)) {
                continue;
            }

            /** @var PhpDocTagNode $children */
            $children = $this->phpDocNode->children[$key];
            $this->cleanChildrenValueType($children, $key);

            $this->markAsChanged();
        }
    }

    /**
     * @return array<string, Type>
     */
    public function getParamTypesByName(): array
    {
        $paramTypesByName = [];

        foreach ($this->phpDocNode->getParamTagValues() as $paramTagValueNode) {
            $parameterName = $paramTagValueNode->parameterName;
            $paramTypesByName[$parameterName] = $this->staticTypeMapper->mapPHPStanPhpDocTypeToPHPStanType(
                $paramTagValueNode,
                $this->node
            );
        }

        return $paramTypesByName;
    }

    public function addTagValueNode(PhpDocTagValueNode $phpDocTagValueNode): void
    {
        if ($phpDocTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            $spacelessPhpDocTagNode = new SpacelessPhpDocTagNode(
                '@\\' . $phpDocTagValueNode->identifierTypeNode,
                $phpDocTagValueNode
            );
            $this->addPhpDocTagNode($spacelessPhpDocTagNode);
            return;
        }

        $name = $this->resolveNameForPhpDocTagValueNode($phpDocTagValueNode);

        $phpDocTagNode = new PhpDocTagNode($name, $phpDocTagValueNode);
        $this->addPhpDocTagNode($phpDocTagNode);
    }

    public function isNewNode(): bool
    {
        if ($this->phpDocNode->children === []) {
            return false;
        }

        return $this->betterTokenIterator->count() === 0;
    }

    public function makeSingleLined(): void
    {
        $this->isSingleLine = true;
    }

    public function isSingleLine(): bool
    {
        return $this->isSingleLine;
    }

    public function hasInvalidTag(string $name): bool
    {
        // fallback for invalid tag value node
        foreach ($this->phpDocNode->children as $phpDocChildNode) {
            if (! $phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }

            if ($phpDocChildNode->name !== $name) {
                continue;
            }

            if (! $phpDocChildNode->value instanceof InvalidTagValueNode) {
                continue;
            }

            return true;
        }

        return false;
    }

    public function getReturnTagValue(): ?ReturnTagValueNode
    {
        $returnTagValueNodes = $this->phpDocNode->getReturnTagValues();
        return $returnTagValueNodes[0] ?? null;
    }

    public function getParamTagValueByName(string $name): ?ParamTagValueNode
    {
        $desiredParamNameWithDollar = '$' . ltrim($name, '$');

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
    public function getTemplateTagValueNodes(): array
    {
        return $this->phpDocNode->getTemplateTagValues();
    }

    public function hasInheritDoc(): bool
    {
        return $this->hasByNames(['inheritdoc', 'inheritDoc']);
    }

    /**
     * @deprecated
     * Should be handled by attributes of phpdoc node - if stard_and_end is missing in one of nodes, it has been changed
     * Similar to missing original node in php-aprser
     */
    public function markAsChanged(): void
    {
        $this->hasChanged = true;

        $node = $this->currentNodeProvider->getNode();
        if ($node !== null) {
            $this->rectorChangeCollector->notifyNodeFileInfo($node);
        }
    }

    public function hasChanged(): bool
    {
        if ($this->isNewNode()) {
            return true;
        }

        if ($this->hasChanged) {
            return true;
        }

        // has a single node with missing start_end
        $phpDocNodeTraverser = new PhpDocNodeTraverser();
        $changedPhpDocNodeVisitor = new ChangedPhpDocNodeVisitor();
        $phpDocNodeTraverser->addPhpDocNodeVisitor($changedPhpDocNodeVisitor);
        $phpDocNodeTraverser->traverse($this->phpDocNode);

        return $changedPhpDocNodeVisitor->hasChanged();
    }

    /**
     * @return string[]
     */
    public function getMethodTagNames(): array
    {
        $methodTagNames = [];
        foreach ($this->phpDocNode->getMethodTagValues() as $methodTagValueNode) {
            $methodTagNames[] = $methodTagValueNode->methodName;
        }

        return $methodTagNames;
    }

    public function makeMultiLined(): void
    {
        $this->isSingleLine = false;
    }

    public function getNode(): \PhpParser\Node
    {
        return $this->node;
    }

    private function cleanChildrenValueType(PhpDocTagNode $phpDocTagNode, int $key): void
    {
        /** @var PhpDocTagValueNode $value */
        $value = $phpDocTagNode->value;
        $type = $value->type;

        $newChildrenTypes = [];
        if ($type instanceof BracketsAwareUnionTypeNode) {
            $brackedTypes = $type->types;
            foreach ($brackedTypes as $brackedType) {
                if ($brackedType instanceof GenericTypeNode) {
                    $newChildrenTypes[] = $brackedType;
                }
            }
        }

        if ($newChildrenTypes === []) {
            unset($this->phpDocNode->children[$key]);
        } else {
            $this->phpDocNode->children[$key]->value->type = new BracketsAwareUnionTypeNode($newChildrenTypes);
        }
    }

    private function getTypeOrMixed(?PhpDocTagValueNode $phpDocTagValueNode): Type
    {
        if ($phpDocTagValueNode === null) {
            return new MixedType();
        }

        return $this->staticTypeMapper->mapPHPStanPhpDocTypeToPHPStanType($phpDocTagValueNode, $this->node);
    }

    private function resolveNameForPhpDocTagValueNode(PhpDocTagValueNode $phpDocTagValueNode): string
    {
        foreach (self::TAGS_TYPES_TO_NAMES as $tagValueNodeType => $name) {
            /** @var class-string<PhpDocTagNode> $tagValueNodeType */
            if (is_a($phpDocTagValueNode, $tagValueNodeType, true)) {
                return $name;
            }
        }

        throw new NotImplementedYetException($phpDocTagValueNode::class);
    }

    /**
     * @return DoctrineAnnotationTagValueNode[]
     */
    private function filterDoctrineTagValuesNodesINcludingNested(string $desiredClass): array
    {
        $desiredDoctrineTagValueNodes = [];

        $doctrineTagValueNodes = $this->getDoctrineTagValueNodesNestedIncluded();
        foreach ($doctrineTagValueNodes as $doctrineTagValueNode) {
            if ($this->doctrineAnnotationMatcher->matches($doctrineTagValueNode, $desiredClass)) {
                $desiredDoctrineTagValueNodes[] = $doctrineTagValueNode;
            }
        }

        return $desiredDoctrineTagValueNodes;
    }

    /**
     * @return DoctrineAnnotationTagValueNode[]
     */
    private function getDoctrineTagValueNodesNestedIncluded(): array
    {
        $doctrineTagValueNodes = [];

        foreach ($this->phpDocNode->children as $phpDocChildNode) {
            if (! $phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }

            if (! $phpDocChildNode->value instanceof DoctrineAnnotationTagValueNode) {
                continue;
            }

            $doctrineTagValueNodes[] = $phpDocChildNode->value;
        }

        // search nested tags too
        $nestedDoctrineTagValueNodes = $this->resolveNestedDoctrineTagValueNodes($doctrineTagValueNodes);

        return array_merge($doctrineTagValueNodes, $nestedDoctrineTagValueNodes);
    }

    /**
     * @param DoctrineAnnotationTagValueNode[] $doctrineTagValueNodes
     * @return DoctrineAnnotationTagValueNode[]
     */
    private function resolveNestedDoctrineTagValueNodes(array $doctrineTagValueNodes): array
    {
        $nestedDoctrineAnnotationTagValueNodes = [];

        foreach ($doctrineTagValueNodes as $doctrineTagValueNode) {
            foreach ($doctrineTagValueNode->getValues() as $nestedTagValue) {
                if (! $nestedTagValue instanceof CurlyListNode) {
                    continue;
                }

                foreach ($nestedTagValue->getValues() as $nestedTagValueNestedValue) {
                    if (! $nestedTagValueNestedValue instanceof DoctrineAnnotationTagValueNode) {
                        continue;
                    }

                    $nestedDoctrineAnnotationTagValueNodes[] = $nestedTagValueNestedValue;
                }
            }
        }

        return $nestedDoctrineAnnotationTagValueNodes;
    }
}
