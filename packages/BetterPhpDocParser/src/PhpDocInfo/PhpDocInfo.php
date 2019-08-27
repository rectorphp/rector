<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocInfo;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Rector\BetterPhpDocParser\Annotation\AnnotationNaming;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwareParamTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwarePhpDocNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwareReturnTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwareVarTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\Attribute;
use Rector\BetterPhpDocParser\Attributes\Contract\Ast\AttributeAwareNodeInterface;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Class_\EntityTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\ColumnTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\JoinColumnTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\ManyToManyTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\ManyToOneTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\OneToManyTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\OneToOneTagValueNode;
use Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc\RelationTagValueNodeInterface;

final class PhpDocInfo
{
    /**
     * @var string
     */
    private $originalContent;

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
     * @param mixed[] $tokens
     */
    public function __construct(
        AttributeAwarePhpDocNode $attributeAwarePhpDocNode,
        array $tokens,
        string $originalContent
    ) {
        $this->phpDocNode = $attributeAwarePhpDocNode;
        $this->tokens = $tokens;
        $this->originalPhpDocNode = clone $attributeAwarePhpDocNode;
        $this->originalContent = $originalContent;
    }

    public function getOriginalContent(): string
    {
        return $this->originalContent;
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

    public function getVarTagValue(): ?AttributeAwareVarTagValueNode
    {
        return $this->getPhpDocNode()->getVarTagValues()[0] ?? null;
    }

    public function getReturnTagValue(): ?AttributeAwareReturnTagValueNode
    {
        return $this->getPhpDocNode()->getReturnTagValues()[0] ?? null;
    }

    /**
     * @return AttributeAwareParamTagValueNode[]
     */
    public function getParamTagValues(): array
    {
        return $this->getPhpDocNode()->getParamTagValues();
    }

    public function hasTag(string $name): bool
    {
        return (bool) $this->getTagsByName($name);
    }

    /**
     * @return PhpDocTagNode[]
     */
    public function getTagsByName(string $name): array
    {
        $name = AnnotationNaming::normalizeName($name);

        /** @var AttributeAwareNodeInterface[]|PhpDocTagNode[] $tags */
        $tags = $this->phpDocNode->getTags();

        $tags = array_filter($tags, function (PhpDocTagNode $tag) use ($name): bool {
            if ($tag->name === $name) {
                return true;
            }

            /** @var PhpDocTagNode|AttributeAwareNodeInterface $tag */
            $annotationClass = $tag->getAttribute(Attribute::ANNOTATION_CLASS);
            if ($annotationClass === null) {
                return false;
            }

            return AnnotationNaming::normalizeName($annotationClass) === $name;
        });

        return array_values($tags);
    }

    /**
     * @return AttributeAwareNodeInterface|TypeNode
     */
    public function getParamTypeNode(string $paramName): ?TypeNode
    {
        $paramName = '$' . ltrim($paramName, '$');

        foreach ($this->phpDocNode->getParamTagValues() as $paramTagsValue) {
            if ($paramTagsValue->parameterName === $paramName) {
                return $paramTagsValue->type;
            }
        }

        return null;
    }

    // types

    /**
     * @return string[]
     */
    public function getParamTypes(string $name): array
    {
        $paramTagValue = $this->getParamTagValueByName($name);
        if ($paramTagValue === null) {
            return [];
        }

        return $this->getResolvedTypesAttribute($paramTagValue);
    }

    /**
     * @return string[]
     */
    public function getVarTypes(): array
    {
        $varTagValue = $this->getVarTagValue();
        if ($varTagValue === null) {
            return [];
        }

        return $this->getResolvedTypesAttribute($varTagValue);
    }

    public function getDoctrineManyToManyTagValueNode(): ?ManyToManyTagValueNode
    {
        return $this->matchChildValueNodeOfType(ManyToManyTagValueNode::class);
    }

    public function getDoctrineManyToOneTagValueNode(): ?ManyToOneTagValueNode
    {
        return $this->matchChildValueNodeOfType(ManyToOneTagValueNode::class);
    }

    public function getDoctrineOneToOneTagValueNode(): ?OneToOneTagValueNode
    {
        return $this->matchChildValueNodeOfType(OneToOneTagValueNode::class);
    }

    public function getDoctrineOneToManyTagValueNode(): ?OneToManyTagValueNode
    {
        return $this->matchChildValueNodeOfType(OneToManyTagValueNode::class);
    }

    public function getDoctrineEntityTag(): ?EntityTagValueNode
    {
        return $this->matchChildValueNodeOfType(EntityTagValueNode::class);
    }

    public function getDoctrineColumnTagValueNode(): ?ColumnTagValueNode
    {
        return $this->matchChildValueNodeOfType(ColumnTagValueNode::class);
    }

    public function getDoctrineJoinColumnTagValueNode(): ?JoinColumnTagValueNode
    {
        return $this->matchChildValueNodeOfType(JoinColumnTagValueNode::class);
    }

    /**
     * @return string[]
     */
    public function getShortVarTypes(): array
    {
        $varTagValue = $this->getVarTagValue();
        if ($varTagValue === null) {
            return [];
        }

        return $varTagValue->getAttribute(Attribute::TYPE_AS_ARRAY) ?: [];
    }

    /**
     * @return string[]
     */
    public function getShortReturnTypes(): array
    {
        $returnTypeValueNode = $this->getReturnTagValue();
        if ($returnTypeValueNode === null) {
            return [];
        }

        return $returnTypeValueNode->getAttribute(Attribute::TYPE_AS_ARRAY) ?: [];
    }

    /**
     * @return string[]
     */
    public function getReturnTypes(): array
    {
        $returnTypeValueNode = $this->getReturnTagValue();
        if ($returnTypeValueNode === null) {
            return [];
        }

        return $this->getResolvedTypesAttribute($returnTypeValueNode);
    }

    public function getRelationTagValueNode(): ?RelationTagValueNodeInterface
    {
        return $this->getDoctrineManyToManyTagValueNode() ??
            $this->getDoctrineOneToManyTagValueNode() ??
            $this->getDoctrineOneToOneTagValueNode() ??
            $this->getDoctrineManyToOneTagValueNode() ?? null;
    }

    private function getParamTagValueByName(string $name): ?AttributeAwareParamTagValueNode
    {
        $phpDocNode = $this->getPhpDocNode();

        /** @var AttributeAwareParamTagValueNode $paramTagValue */
        foreach ($phpDocNode->getParamTagValues() as $paramTagValue) {
            if (Strings::match($paramTagValue->parameterName, '#^(\$)?' . $name . '$#')) {
                return $paramTagValue;
            }
        }

        return null;
    }

    /**
     * @param PhpDocTagValueNode|AttributeAwareNodeInterface $phpDocTagValueNode
     * @return string[]
     */
    private function getResolvedTypesAttribute(PhpDocTagValueNode $phpDocTagValueNode): array
    {
        if ($phpDocTagValueNode->getAttribute(Attribute::RESOLVED_NAMES)) {
            return $phpDocTagValueNode->getAttribute(Attribute::RESOLVED_NAMES);
        }

        return $phpDocTagValueNode->getAttribute(Attribute::TYPE_AS_ARRAY);
    }

    /**
     * @param string $type
     */
    private function matchChildValueNodeOfType(string $type): ?PhpDocTagValueNode
    {
        foreach ($this->phpDocNode->children as $phpDocChildNode) {
            if ($phpDocChildNode instanceof PhpDocTagNode) {
                if (is_a($phpDocChildNode->value, $type, true)) {
                    return $phpDocChildNode->value;
                }
            }
        }

        return null;
    }
}
