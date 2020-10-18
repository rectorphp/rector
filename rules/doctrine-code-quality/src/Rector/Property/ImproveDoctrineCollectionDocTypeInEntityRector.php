<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareArrayTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareIdentifierTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareUnionTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\DoctrineCodeQuality\NodeAnalyzer\DoctrinePropertyAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\DoctrineCodeQuality\Tests\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector\ImproveDoctrineCollectionDocTypeInEntityRectorTest
 */
final class ImproveDoctrineCollectionDocTypeInEntityRector extends AbstractRector
{
    /**
     * @var DoctrinePropertyAnalyzer
     */
    private $doctrinePropertyAnalyzer;

    public function __construct(DoctrinePropertyAnalyzer $doctrinePropertyAnalyzer)
    {
        $this->doctrinePropertyAnalyzer = $doctrinePropertyAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Improve @var, @param and @return types for Doctrine collections to make them useful both for PHPStan and PHPStorm',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @ORM\OneToMany(targetEntity=Training::class, mappedBy="trainer")
     * @var Collection|Trainer[]
     */
    private $trainings = [];
}
CODE_SAMPLE

                    ,
                    <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @ORM\OneToMany(targetEntity=Training::class, mappedBy="trainer")
     * @var Collection<int, Training>|Trainer[]
     */
    private $trainings = [];
}
CODE_SAMPLE

                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }

    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        $attributeAwareVarTagValueNode = $this->resolveCollectionVarTagValueNode($node);
        if ($attributeAwareVarTagValueNode === null) {
            return null;
        }

        $collectionObjectType = $this->resolveCollectionObjectType($attributeAwareVarTagValueNode->type);
        if ($collectionObjectType === null) {
            return null;
        }

        $attributeAwareVarTagValueNode->type = $this->createCollectionUnionType($collectionObjectType);

        return $node;
    }

    private function resolveCollectionVarTagValueNode(Property $property): ?VarTagValueNode
    {
        $doctrineOneToManyTagValueNode = $this->doctrinePropertyAnalyzer->matchDoctrineOneToManyTagValueNode($property);
        if ($doctrineOneToManyTagValueNode === null) {
            return null;
        }

        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        if (! $phpDocInfo instanceof PhpDocInfo) {
            return null;
        }

        return $phpDocInfo->getVarTagValueNode();
    }

    private function resolveCollectionObjectType(TypeNode $typeNode): ?IdentifierTypeNode
    {
        if ($typeNode instanceof UnionTypeNode) {
            foreach ($typeNode->types as $unionedTypeNode) {
                if ($this->resolveCollectionObjectType($unionedTypeNode) !== null) {
                    return $this->resolveCollectionObjectType($unionedTypeNode);
                }
            }
        }

        if ($typeNode instanceof ArrayTypeNode && $typeNode->type instanceof IdentifierTypeNode) {
            return $typeNode->type;
        }

        return null;
    }

    private function createCollectionUnionType(IdentifierTypeNode $identifierTypeNode): AttributeAwareUnionTypeNode
    {
        $genericTypesNodes = [new AttributeAwareIdentifierTypeNode('int'), $identifierTypeNode];
        $genericTypeNode = new GenericTypeNode(new AttributeAwareIdentifierTypeNode('Collection'), $genericTypesNodes);

        return new AttributeAwareUnionTypeNode([
            // new CollectionType
            $genericTypeNode,
            new AttributeAwareArrayTypeNode($identifierTypeNode),
        ]);
    }
}
