<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Doctrine\NodeManipulator\ToOneRelationPropertyTypeResolver;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\TypeDeclaration\NodeTypeAnalyzer\PropertyTypeDecorator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\Rector\Property\TypedPropertyFromToOneRelationTypeRector\TypedPropertyFromToOneRelationTypeRectorTest
 */
final class TypedPropertyFromToOneRelationTypeRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeTypeAnalyzer\PropertyTypeDecorator
     */
    private $propertyTypeDecorator;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeManipulator\ToOneRelationPropertyTypeResolver
     */
    private $toOneRelationPropertyTypeResolver;
    public function __construct(\Rector\TypeDeclaration\NodeTypeAnalyzer\PropertyTypeDecorator $propertyTypeDecorator, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger $phpDocTypeChanger, \Rector\Doctrine\NodeManipulator\ToOneRelationPropertyTypeResolver $toOneRelationPropertyTypeResolver)
    {
        $this->propertyTypeDecorator = $propertyTypeDecorator;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->toOneRelationPropertyTypeResolver = $toOneRelationPropertyTypeResolver;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Complete @var annotations or types based on @ORM\\*toOne annotations or attributes', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

class SimpleColumn
{
    /**
     * @ORM\OneToOne(targetEntity="App\Company\Entity\Company")
     */
    private $company;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

class SimpleColumn
{
    /**
     * @ORM\OneToOne(targetEntity="App\Company\Entity\Company")
     */
    private ?\App\Company\Entity\Company $company = null;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Property::class];
    }
    /**
     * @param Property $node
     * @return \PhpParser\Node\Stmt\Property|null
     */
    public function refactor(\PhpParser\Node $node)
    {
        if ($node->type !== null) {
            return null;
        }
        $propertyType = $this->toOneRelationPropertyTypeResolver->resolve($node);
        if (!$propertyType instanceof \PHPStan\Type\Type) {
            return null;
        }
        if ($propertyType instanceof \PHPStan\Type\MixedType) {
            return null;
        }
        $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::PROPERTY());
        if ($typeNode === null) {
            return null;
        }
        $this->completePropertyTypeOrVarDoc($propertyType, $typeNode, $node);
        return $node;
    }
    /**
     * @param \PhpParser\Node\ComplexType|\PhpParser\Node\Name $typeNode
     */
    private function completePropertyTypeOrVarDoc(\PHPStan\Type\Type $propertyType, $typeNode, \PhpParser\Node\Stmt\Property $property) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        if ($this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersion::PHP_74)) {
            if ($propertyType instanceof \PHPStan\Type\UnionType) {
                $this->propertyTypeDecorator->decoratePropertyUnionType($propertyType, $typeNode, $property, $phpDocInfo);
                return;
            }
            $property->type = $typeNode;
            return;
        }
        $this->phpDocTypeChanger->changeVarType($phpDocInfo, $propertyType);
    }
}
