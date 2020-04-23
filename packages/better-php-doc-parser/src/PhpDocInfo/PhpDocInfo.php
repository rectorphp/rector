<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocInfo;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Param;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareParamTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocTagNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareReturnTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareVarTagValueNode;
use Rector\BetterPhpDocParser\Annotation\AnnotationNaming;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\SpacelessPhpDocTagNode;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TypeAwareTagValueNodeInterface;

use Rector\Core\Exception\NotImplementedException;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\PHPStan\TypeComparator;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\PHPStan\Type\ShortenedObjectType;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\PhpDocParser\ParamPhpDocNodeFactory;

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
     * @var TypeComparator
     */
    private $typeComparator;

    /**
     * @var ParamPhpDocNodeFactory
     */
    private $paramPhpDocNodeFactory;

    /**
     * @param mixed[] $tokens
     */
    public function __construct(
        AttributeAwarePhpDocNode $attributeAwarePhpDocNode,
        array $tokens,
        string $originalContent,
        StaticTypeMapper $staticTypeMapper,
        Node $node,
        TypeComparator $typeComparator,
        ParamPhpDocNodeFactory $paramPhpDocNodeFactory
    ) {
        $this->phpDocNode = $attributeAwarePhpDocNode;
        $this->tokens = $tokens;
        $this->originalPhpDocNode = clone $attributeAwarePhpDocNode;
        $this->originalContent = $originalContent;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->node = $node;
        $this->typeComparator = $typeComparator;
        $this->paramPhpDocNodeFactory = $paramPhpDocNodeFactory;
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

    public function getVarTagValue(): ?AttributeAwareVarTagValueNode
    {
        return $this->phpDocNode->getVarTagValues()[0] ?? null;
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
            return $tag->name === $name;
        });

        return array_values($tags);
    }

    public function getParamType(string $name): Type
    {
        $paramTagValue = $this->getParamTagValueByName($name);
        return $this->getTypeOrMixed($paramTagValue);
    }

    /**
     * @return Type[]
     */
    public function getParamTypes(): array
    {
        $paramTypes = [];
        foreach ($this->phpDocNode->getParamTagValues() as $paramTagValue) {
            $paramTypes[] = $this->staticTypeMapper->mapPHPStanPhpDocTypeToPHPStanType($paramTagValue, $this->node);
        }

        return $paramTypes;
    }

    public function getVarType(): Type
    {
        return $this->getTypeOrMixed($this->getVarTagValue());
    }

    public function getReturnType(): Type
    {
        return $this->getTypeOrMixed($this->getReturnTagValue());
    }

    public function removeTagValueNodeFromNode(PhpDocTagValueNode $phpDocTagValueNode): void
    {
        foreach ($this->phpDocNode->children as $key => $phpDocChildNode) {
            if (! $phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }

            if ($phpDocChildNode->value !== $phpDocTagValueNode) {
                continue;
            }

            unset($this->phpDocNode->children[$key]);
        }
    }

    public function hasByType(string $type): bool
    {
        return (bool) $this->getByType($type);
    }

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

    public function removeByName(string $name): void
    {
        foreach ($this->phpDocNode->children as $key => $phpDocChildNode) {
            if (! $phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }

            if (! $this->areAnnotationNamesEqual($name, $phpDocChildNode->name)) {
                continue;
            }

            unset($this->phpDocNode->children[$key]);
        }
    }

    /**
     * With "name" as key
     * @return Type[]
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

    public function changeVarType(Type $newType): void
    {
        // make sure the tags are not identical, e.g imported class vs FQN class
        if ($this->typeComparator->areTypesEquals($this->getVarType(), $newType)) {
            return;
        }

        // prevent existing type override by mixed
        if (! $this->getVarType() instanceof MixedType && $newType instanceof ConstantArrayType && $newType->getItemType() instanceof NeverType) {
            return;
        }

        // override existing type
        $newPHPStanPhpDocType = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($newType);

        $currentVarTagValueNode = $this->getVarTagValue();
        if ($currentVarTagValueNode !== null) {
            // only change type
            $currentVarTagValueNode->type = $newPHPStanPhpDocType;
        } else {
            // add completely new one
            $returnTagValueNode = new AttributeAwareVarTagValueNode($newPHPStanPhpDocType, '', '');
            $this->addTagValueNode($returnTagValueNode);
        }
    }

    public function changeReturnType(Type $newType): void
    {
        // make sure the tags are not identical, e.g imported class vs FQN class
        if ($this->typeComparator->areTypesEquals($this->getReturnType(), $newType)) {
            return;
        }

        // override existing type
        $newPHPStanPhpDocType = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($newType);

        $currentReturnTagValueNode = $this->getReturnTagValue();
        if ($currentReturnTagValueNode !== null) {
            // only change type
            $currentReturnTagValueNode->type = $newPHPStanPhpDocType;
        } else {
            // add completely new one
            $returnTagValueNode = new AttributeAwareReturnTagValueNode($newPHPStanPhpDocType, '');
            $this->addTagValueNode($returnTagValueNode);
        }
    }

    public function addBareTag(string $tag): void
    {
        $tag = '@' . ltrim($tag, '@');

        $phpDocTagNode = new AttributeAwarePhpDocTagNode($tag, new GenericTagValueNode(''));
        $this->addPhpDocTagNode($phpDocTagNode);
    }

    public function addTagValueNode(PhpDocTagValueNode $phpDocTagValueNode): void
    {
        $name = $this->resolveNameForPhpDocTagValueNode($phpDocTagValueNode);

        $phpDocTagNode = new AttributeAwarePhpDocTagNode($name, $phpDocTagValueNode);
        $this->addPhpDocTagNode($phpDocTagNode);
    }

    public function isNewNode(): bool
    {
        if ($this->phpDocNode->children === []) {
            return false;
        }

        return $this->tokens === [];
    }

    public function changeParamType(Type $type, Param $param, string $paramName): void
    {
        $paramTagValueNode = $this->getParamTagValueByName($paramName);

        $phpDocType = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($type);

        // override existing type
        if ($paramTagValueNode !== null) {
            $paramTagValueNode->type = $phpDocType;
            return;
        }

        $paramTagValueNode = $this->paramPhpDocNodeFactory->create($type, $param);
        $this->addTagValueNode($paramTagValueNode);
    }

    /**
     * @return string[]
     */
    public function getThrowsClassNames(): array
    {
        $throwsClasses = [];
        foreach ($this->getThrowsTypes() as $throwsType) {
            if ($throwsType instanceof ShortenedObjectType) {
                $throwsClasses[] = $throwsType->getFullyQualifiedName();
            }

            if ($throwsType instanceof FullyQualifiedObjectType) {
                $throwsClasses[] = $throwsType->getClassName();
            }
        }

        return $throwsClasses;
    }

    /**
     * @return Type[]
     */
    private function getThrowsTypes(): array
    {
        $throwsTypes = [];

        foreach ($this->getTagsByName('throws') as $throwsPhpDocNode) {
            if (! $throwsPhpDocNode->value instanceof ThrowsTagValueNode) {
                continue;
            }

            $throwsTypes[] = $this->staticTypeMapper->mapPHPStanPhpDocTypeToPHPStanType(
                $throwsPhpDocNode->value,
                $this->node
            );
        }

        return $throwsTypes;
    }

    private function getTypeOrMixed(?PhpDocTagValueNode $phpDocTagValueNode)
    {
        if ($phpDocTagValueNode === null) {
            return new MixedType();
        }

        return $this->staticTypeMapper->mapPHPStanPhpDocTypeToPHPStanType($phpDocTagValueNode, $this->node);
    }

    private function getReturnTagValue(): ?AttributeAwareReturnTagValueNode
    {
        return $this->phpDocNode->getReturnTagValues()[0] ?? null;
    }

    private function getParamTagValueByName(string $name): ?AttributeAwareParamTagValueNode
    {
        /** @var AttributeAwareParamTagValueNode $paramTagValue */
        foreach ($this->phpDocNode->getParamTagValues() as $paramTagValue) {
            if (Strings::match($paramTagValue->parameterName, '#^(\$)?' . $name . '$#')) {
                return $paramTagValue;
            }
        }

        return null;
    }

    private function ensureTypeIsTagValueNode(string $type, string $location): void
    {
        if (is_a($type, PhpDocTagValueNode::class, true)) {
            return;
        }

        if (is_a($type, TypeAwareTagValueNodeInterface::class, true)) {
            return;
        }

        throw new ShouldNotHappenException(sprintf(
            'Type "%s" passed to "%s()" method must be child of "%s"',
            $type,
            $location,
            PhpDocTagValueNode::class
        ));
    }

    private function areAnnotationNamesEqual(string $firstAnnotationName, string $secondAnnotationName): bool
    {
        $firstAnnotationName = trim($firstAnnotationName, '@');
        $secondAnnotationName = trim($secondAnnotationName, '@');

        return $firstAnnotationName === $secondAnnotationName;
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
}
