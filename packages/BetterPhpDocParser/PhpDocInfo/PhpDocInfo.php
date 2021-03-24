<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocInfo;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\CustomIdGenerator;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation\Blameable;
use Gedmo\Mapping\Annotation\Slug;
use PHPStan\PhpDocParser\Ast\Node;
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
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\Annotation\AnnotationNaming;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\SpacelessPhpDocTagNode;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\ClassNameAwareTagInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
<<<<<<< HEAD
use Rector\BetterPhpDocParser\ValueObject\NodeTypes;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\AbstractTagValueNode;
use Rector\ChangesReporting\Collector\RectorChangeCollector;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Doctrine\PhpDoc\Node\Class_\EmbeddedTagValueNode;
use Rector\Doctrine\PhpDoc\Node\Class_\EntityTagValueNode;
use Rector\Doctrine\PhpDoc\Node\Class_\TableTagValueNode;
use Rector\Doctrine\PhpDoc\Node\Gedmo\BlameableTagValueNode;
use Rector\Doctrine\PhpDoc\Node\Gedmo\SlugTagValueNode;
use Rector\Doctrine\PhpDoc\Node\Property_\ColumnTagValueNode;
use Rector\Doctrine\PhpDoc\Node\Property_\CustomIdGeneratorTagValueNode;
use Rector\Doctrine\PhpDoc\Node\Property_\GeneratedValueTagValueNode;
use Rector\Doctrine\PhpDoc\Node\Property_\JoinTableTagValueNode;
=======
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\ChangesReporting\Collector\RectorChangeCollector;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\Core\Exception\NotImplementedYetException;
>>>>>>> 4100b04a63... Refactor doctrine/annotation parser to static reflection with phpdoc-parser
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\Symfony\PhpDoc\Node\AssertChoiceTagValueNode;
use Rector\Symfony\PhpDoc\Node\AssertTypeTagValueNode;
use Rector\Symfony\PhpDoc\Node\Sensio\SensioMethodTagValueNode;
use Rector\Symfony\PhpDoc\Node\Sensio\SensioTemplateTagValueNode;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Validator\Constraints\Choice;

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
     * @var PhpDocNode
     */
    private $phpDocNode;

    /**
     * @var PhpDocNode
     */
    private $originalPhpDocNode;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var \PhpParser\Node
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
        PhpDocNode $phpDocNode,
        array $tokens,
        string $originalContent,
        StaticTypeMapper $staticTypeMapper,
        \PhpParser\Node $node,
        AnnotationNaming $annotationNaming,
        CurrentNodeProvider $currentNodeProvider,
        RectorChangeCollector $rectorChangeCollector
    ) {
        $this->phpDocNode = $phpDocNode;
        $this->tokens = $tokens;
        $this->originalPhpDocNode = clone $phpDocNode;
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
        $this->markAsChanged();
    }

    public function addTagValueNodeWithShortName(ShortNameAwareTagInterface $shortNameAwareTag): void
    {
        $spacelessPhpDocTagNode = new SpacelessPhpDocTagNode($shortNameAwareTag->getShortName(), $shortNameAwareTag);
        $this->addPhpDocTagNode($spacelessPhpDocTagNode);
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
     * @return array<PhpDocTagNode>
     */
    public function getTagsByName(string $name): array
    {
        $name = $this->annotationNaming->normalizeName($name);

        $tags = $this->phpDocNode->getTags();

        $tags = array_filter($tags, function (PhpDocTagNode $tag) use ($name): bool {
            return $tag->name === $name;
        });

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

    public function getVarType(): Type
    {
        return $this->getTypeOrMixed($this->getVarTagValueNode());
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
     * @param class-string<TNode>[] $types
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

    /**
     * @param array<class-string<TNode|class-string>> $types
     * @return TNode|null
     */
    public function getByTypes(array $types)
    {
        foreach ($types as $type) {
            $phpDocNode = $this->getByType($type);
            if ($phpDocNode !== null) {
                return $phpDocNode;
            }
        }

        return null;
    }

    /**
     * @param class-string<TNode|class-string> $type
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

            // new approach
            if ($phpDocChildNode->value instanceof DoctrineAnnotationTagValueNode) {
                if ($phpDocChildNode->value->getTagClass() === $type) {
                    return $phpDocChildNode->value;
                }
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
     * @return array<T>
     */
    public function findAllByType(string $type): array
    {
//        $this->ensureTypeIsTagValueNode($type, __METHOD__);

        $foundTagsValueNodes = [];

        foreach ($this->phpDocNode->children as $phpDocChildNode) {
            if (is_a($phpDocChildNode, $type, true)) {
                $foundTagsValueNodes[] = $phpDocChildNode;
                continue;
            }

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

        /** @var Node[] $foundTagsValueNodes */
        return $foundTagsValueNodes;
    }

    /**
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

            unset($this->phpDocNode->children[$key]);
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
        if ($phpDocTagValueNode instanceof ClassNameAwareTagInterface) {
            $spacelessPhpDocTagNode = new SpacelessPhpDocTagNode(
                '@\\' . $phpDocTagValueNode->getClassName(),
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

        return $this->tokens === [];
    }

    public function makeSingleLined(): void
    {
        $this->isSingleLine = true;
    }

    public function isSingleLine(): bool
    {
        return $this->isSingleLine;
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

        return $this->hasChanged;
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

<<<<<<< HEAD
    public function hasByAnnotationClass(string $annotationClass): bool
    {
        $tagValueNodes = $this->findAllByType(AbstractTagValueNode::class);
        foreach ($tagValueNodes as $tagValueNode) {
            // temporary hack
            if ($tagValueNode instanceof CustomIdGeneratorTagValueNode) {
                $tagClassName = CustomIdGenerator::class;
            } elseif ($tagValueNode instanceof ColumnTagValueNode) {
                $tagClassName = Column::class;
            } elseif ($tagValueNode instanceof GeneratedValueTagValueNode) {
                $tagClassName = GeneratedValue::class;
            } elseif ($tagValueNode instanceof AssertChoiceTagValueNode) {
                $tagClassName = Choice::class;
            } elseif ($tagValueNode instanceof JoinTableTagValueNode) {
                $tagClassName = JoinTable::class;
            } elseif ($tagValueNode instanceof AssertTypeTagValueNode) {
                $tagClassName = \Symfony\Component\Validator\Constraints\Type::class;
            } elseif ($tagValueNode instanceof TableTagValueNode) {
                $tagClassName = Table::class;
            } elseif ($tagValueNode instanceof SlugTagValueNode) {
                $tagClassName = Slug::class;
            } elseif ($tagValueNode instanceof BlameableTagValueNode) {
                $tagClassName = Blameable::class;
            } elseif ($tagValueNode instanceof EntityTagValueNode) {
                $tagClassName = Entity::class;
            } elseif ($tagValueNode instanceof EmbeddedTagValueNode) {
                $tagClassName = Embedded::class;
            } elseif ($tagValueNode instanceof SensioMethodTagValueNode) {
                $tagClassName = Method::class;
            } elseif ($tagValueNode instanceof SensioTemplateTagValueNode) {
                $tagClassName = Template::class;
            } elseif (defined(get_class($tagValueNode) . '::CLASS_NAME')) {
                $tagClassName = $tagValueNode::CLASS_NAME;
            } else {
                continue;
            }

            if ($tagClassName === $annotationClass) {
                return true;
            }
        }

        return false;
    }

    private function getTypeOrMixed(?PhpDocTagValueNode $phpDocTagValueNode): Type
=======
    /**
     * Looking for class-based annotations from doctrine/annotation
     * @param string $name Full string, or mask string, e.g. "Doctrine\*"
     */
    public function hasTagClassNamed(string $name): bool
>>>>>>> 4100b04a63... Refactor doctrine/annotation parser to static reflection with phpdoc-parser
    {
        foreach ($this->phpDocNode->children as $phpDocChildNode) {
            if (! $phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }

            if (! $phpDocChildNode->value instanceof DoctrineAnnotationTagValueNode) {
                continue;
            }

            if ($phpDocChildNode->value->getTagClass() === $name) {
                return true;
            }

            if (fnmatch($name, $phpDocChildNode->value->getTagClass(), FNM_NOESCAPE)) {
                return true;
            }
        }

        return false;
    }

    private function getTypeOrMixed(?PhpDocTagValueNode $phpDocTagValueNode): Type
    {
        if ($phpDocTagValueNode === null) {
            return new MixedType();
        }

        return $this->staticTypeMapper->mapPHPStanPhpDocTypeToPHPStanType($phpDocTagValueNode, $this->node);
    }

//    /**
//     * @param class-string $phpDocTagValueNode
//     */
//    private function ensureTypeIsTagValueNode(string $type, string $location): void
//    {
//        /** @var array<class-string<\PhpParser\Node>> $desiredTypes */
//        $desiredTypes = array_merge([
//            PhpDocTagValueNode::class,
//            PhpDocTagNode::class,
//        ], NodeTypes::TYPE_AWARE_NODES);
//
//        foreach ($desiredTypes as $desiredType) {
//            if (is_a($type, $desiredType, true)) {
//                return;
//            }
//        }
//
//        throw new ShouldNotHappenException(sprintf(
//            'Type "%s" passed to "%s()" method must be child of "%s"',
//            $type,
//            $location,
//            PhpDocTagValueNode::class

//        ));

//    }

    private function resolveNameForPhpDocTagValueNode(PhpDocTagValueNode $phpDocTagValueNode): string
    {
        foreach (self::TAGS_TYPES_TO_NAMES as $tagValueNodeType => $name) {
            /** @var class-string<PhpDocTagNode> $tagValueNodeType */
            if (is_a($phpDocTagValueNode, $tagValueNodeType, true)) {
                return $name;
            }
        }

        throw new NotImplementedYetException(get_class($phpDocTagValueNode));
    }
}
