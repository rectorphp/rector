<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocInfo;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareParamTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocTagNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareReturnTagValueNode;
use Rector\BetterPhpDocParser\Annotation\AnnotationNaming;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\SpacelessPhpDocTagNode;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TypeAwareTagValueNodeInterface;
use Rector\ChangesReporting\Collector\RectorChangeCollector;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\Core\Exception\NotImplementedException;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Util\StaticInstanceOf;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;

/**
 * @see \Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfo\PhpDocInfoTest
 */
final class PhpDocInfo
{
    /**
     * @var string
     */
    private $originalContent;

    /**
     * @var bool
     */
    private $isSingleLine = false;

    /**
     * @var mixed[]
     */
    private $tokens = [];

    /**
     * @var AttributeAwarePhpDocNode
     */
    private $phpDocNode;

    /**
     * @var AttributeAwarePhpDocNode
     */
    private $originalPhpDocNode;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var Node
     */
    private $node;

    /**
     * @var bool
     */
    private $hasChanged = false;

    /**
     * @var AnnotationNaming
     */
    private $annotationNaming;

    /**
     * @var CurrentNodeProvider
     */
    private $currentNodeProvider;

    /**
     * @var RectorChangeCollector
     */
    private $rectorChangeCollector;

    /**
     * @param mixed[] $tokens
     */
    public function __construct(
        AttributeAwarePhpDocNode $attributeAwarePhpDocNode,
        array $tokens,
        string $originalContent,
        StaticTypeMapper $staticTypeMapper,
        Node $node,
        AnnotationNaming $annotationNaming,
        CurrentNodeProvider $currentNodeProvider,
        RectorChangeCollector $rectorChangeCollector
    ) {
        $this->phpDocNode = $attributeAwarePhpDocNode;
        $this->tokens = $tokens;
        $this->originalPhpDocNode = clone $attributeAwarePhpDocNode;
        $this->originalContent = $originalContent;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->node = $node;
        $this->annotationNaming = $annotationNaming;
        $this->currentNodeProvider = $currentNodeProvider;
        $this->rectorChangeCollector = $rectorChangeCollector;
    }

    public function getOriginalContent(): string
    {
        return $this->originalContent;
    }

    public function addPhpDocTagNode(PhpDocChildNode $phpDocChildNode): void
    {
        $this->phpDocNode->children[] = $phpDocChildNode;
    }

    public function addTagValueNodeWithShortName(ShortNameAwareTagInterface $shortNameAwareTag): void
    {
        $spacelessPhpDocTagNode = new SpacelessPhpDocTagNode($shortNameAwareTag->getShortName(), $shortNameAwareTag);
        $this->addPhpDocTagNode($spacelessPhpDocTagNode);
    }

    public function getPhpDocNode(): AttributeAwarePhpDocNode
    {
        return $this->phpDocNode;
    }

    public function getOriginalPhpDocNode(): AttributeAwarePhpDocNode
    {
        return $this->originalPhpDocNode;
    }

    /**
     * @return mixed[]
     */
    public function getTokens(): array
    {
        return $this->tokens;
    }

    public function getTokenCount(): int
    {
        return count($this->tokens);
    }

    public function getVarTagValueNode(): ?VarTagValueNode
    {
        return $this->phpDocNode->getVarTagValues()[0] ?? null;
    }

    /**
     * @return PhpDocTagNode[]|AttributeAwareNodeInterface[]
     */
    public function getTagsByName(string $name): array
    {
        $name = $this->annotationNaming->normalizeName($name);

        /** @var PhpDocTagNode[]|AttributeAwareNodeInterface[] $tags */
        $tags = $this->phpDocNode->getTags();

        $tags = array_filter($tags, function (PhpDocTagNode $tag) use ($name): bool {
            return $tag->name === $name;
        });
        $tags = array_values($tags);

        return array_values($tags);
    }

    public function getParamType(string $name): Type
    {
        $attributeAwareParamTagValueNode = $this->getParamTagValueByName($name);
        return $this->getTypeOrMixed($attributeAwareParamTagValueNode);
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

    public function getVarType(): Type
    {
        return $this->getTypeOrMixed($this->getVarTagValueNode());
    }

    public function getReturnType(): Type
    {
        return $this->getTypeOrMixed($this->getReturnTagValue());
    }

    /**
     * @template T as \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode
     * @param class-string<T> $type
     */
    public function hasByType(string $type): bool
    {
        return (bool) $this->getByType($type);
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

    /**
     * @template T as \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode
     * @param class-string<T> $type
     * @return T|null
     */
    public function getByType(string $type): ?PhpDocTagValueNode
    {
        $this->ensureTypeIsTagValueNode($type, __METHOD__);

        foreach ($this->phpDocNode->children as $phpDocChildNode) {
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
     * @template T of \PHPStan\PhpDocParser\Ast\Node
     * @param class-string<T> $type
     * @return T[]
     */
    public function findAllByType(string $type): array
    {
        $this->ensureTypeIsTagValueNode($type, __METHOD__);

        $foundTagsValueNodes = [];

        foreach ($this->phpDocNode->children as $phpDocChildNode) {
            if (! $phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }

            if ($type === PhpDocTagNode::class) {
                $foundTagsValueNodes[] = $phpDocChildNode;
                continue;
            }

            if (! is_a($phpDocChildNode->value, $type, true)) {
                continue;
            }

            $foundTagsValueNodes[] = $phpDocChildNode->value;
        }

        return $foundTagsValueNodes;
    }

    public function removeByType(string $type): void
    {
        $this->ensureTypeIsTagValueNode($type, __METHOD__);

        foreach ($this->phpDocNode->children as $key => $phpDocChildNode) {
            if (! $phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }

            if (! is_a($phpDocChildNode->value, $type, true)) {
                continue;
            }

            unset($this->phpDocNode->children[$key]);
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

    public function addBareTag(string $tag): void
    {
        $tag = '@' . ltrim($tag, '@');

        $attributeAwarePhpDocTagNode = new AttributeAwarePhpDocTagNode($tag, new GenericTagValueNode(''));
        $this->addPhpDocTagNode($attributeAwarePhpDocTagNode);
    }

    public function addTagValueNode(PhpDocTagValueNode $phpDocTagValueNode): void
    {
        $name = $this->resolveNameForPhpDocTagValueNode($phpDocTagValueNode);

        $attributeAwarePhpDocTagNode = new AttributeAwarePhpDocTagNode($name, $phpDocTagValueNode);
        $this->addPhpDocTagNode($attributeAwarePhpDocTagNode);
    }

    public function isNewNode(): bool
    {
        if ($this->phpDocNode->children === []) {
            return false;
        }

        return $this->tokens === [];
    }

    /**
     * @return class-string[]
     */
    public function getThrowsClassNames(): array
    {
        $throwsClasses = [];
        foreach ($this->getThrowsTypes() as $throwsType) {
            if ($throwsType instanceof ShortenedObjectType) {
                /** @var class-string $className */
                $className = $throwsType->getFullyQualifiedName();
                $throwsClasses[] = $className;
            }

            if ($throwsType instanceof FullyQualifiedObjectType) {
                /** @var class-string $className */
                $className = $throwsType->getClassName();
                $throwsClasses[] = $className;
            }
        }

        return $throwsClasses;
    }

    public function makeSingleLined(): void
    {
        $this->isSingleLine = true;
    }

    public function isSingleLine(): bool
    {
        return $this->isSingleLine;
    }

    public function getReturnTagValue(): ?AttributeAwareReturnTagValueNode
    {
        /** @var AttributeAwareReturnTagValueNode[] $returnTagValueNodes */
        $returnTagValueNodes = $this->phpDocNode->getReturnTagValues();
        return $returnTagValueNodes[0] ?? null;
    }

    public function getParamTagValueByName(string $name): ?AttributeAwareParamTagValueNode
    {
        return $this->phpDocNode->getParam($name);
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
        return $this->hasChanged;
    }

    private function getTypeOrMixed(?PhpDocTagValueNode $phpDocTagValueNode): Type
    {
        if ($phpDocTagValueNode === null) {
            return new MixedType();
        }

        return $this->staticTypeMapper->mapPHPStanPhpDocTypeToPHPStanType($phpDocTagValueNode, $this->node);
    }

    private function ensureTypeIsTagValueNode(string $type, string $location): void
    {
        if (StaticInstanceOf::isOneOf($type, [
            PhpDocTagValueNode::class,
            PhpDocTagNode::class,
            TypeAwareTagValueNodeInterface::class,
            PhpAttributableTagNodeInterface::class,
        ])) {
            return;
        }

        throw new ShouldNotHappenException(sprintf(
            'Type "%s" passed to "%s()" method must be child of "%s"',
            $type,
            $location,
            PhpDocTagValueNode::class
        ));
    }

    private function resolveNameForPhpDocTagValueNode(PhpDocTagValueNode $phpDocTagValueNode): string
    {
        if ($phpDocTagValueNode instanceof ReturnTagValueNode) {
            return '@return';
        }

        if ($phpDocTagValueNode instanceof ParamTagValueNode) {
            return '@param';
        }

        if ($phpDocTagValueNode instanceof VarTagValueNode) {
            return '@var';
        }

        throw new NotImplementedException();
    }

    /**
     * @return Type[]
     */
    private function getThrowsTypes(): array
    {
        $throwsTypes = [];

        foreach ($this->phpDocNode->getThrowsTagValues() as $throwsTagValueNode) {
            $throwsTypes[] = $this->staticTypeMapper->mapPHPStanPhpDocTypeToPHPStanType(
                $throwsTagValueNode,
                $this->node
            );
        }

        return $throwsTypes;
    }
}
