<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Doctrine\NodeManipulator\ToManyRelationPropertyTypeResolver;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\TypeDeclaration\NodeTypeAnalyzer\PropertyTypeDecorator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\Rector\Property\TypedPropertyFromToManyRelationTypeRector\TypedPropertyFromToManyRelationTypeRectorTest
 */
final class TypedPropertyFromToManyRelationTypeRector extends \Rector\Core\Rector\AbstractRector
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
     * @var \Rector\Doctrine\NodeManipulator\ToManyRelationPropertyTypeResolver
     */
    private $toManyRelationPropertyTypeResolver;
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(\Rector\TypeDeclaration\NodeTypeAnalyzer\PropertyTypeDecorator $propertyTypeDecorator, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger $phpDocTypeChanger, \Rector\Doctrine\NodeManipulator\ToManyRelationPropertyTypeResolver $toManyRelationPropertyTypeResolver, \Rector\Core\Php\PhpVersionProvider $phpVersionProvider)
    {
        $this->propertyTypeDecorator = $propertyTypeDecorator;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->toManyRelationPropertyTypeResolver = $toManyRelationPropertyTypeResolver;
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Complete @var annotations or types based on @ORM\\*toMany annotations or attributes', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

class SimpleColumn
{
    /**
     * @ORM\OneToMany(targetEntity="App\Product")
     */
    private $products;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

class SimpleColumn
{
    /**
     * @ORM\OneToMany(targetEntity="App\Product")
     * @var \Doctrine\Common\Collections\Collection<\App\Product>
     */
    private \Doctrine\Common\Collections\Collection $products;
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
        $propertyType = $this->toManyRelationPropertyTypeResolver->resolve($node);
        if (!$propertyType instanceof \PHPStan\Type\Type || $propertyType instanceof \PHPStan\Type\MixedType) {
            return null;
        }
        $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::PROPERTY);
        if ($typeNode === null) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        // always decorate with collection generic type
        $this->phpDocTypeChanger->changeVarType($phpDocInfo, $propertyType);
        if ($this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersion::PHP_74)) {
            if ($propertyType instanceof \PHPStan\Type\UnionType) {
                $this->propertyTypeDecorator->decoratePropertyUnionType($propertyType, $typeNode, $node, $phpDocInfo);
                return $node;
            }
            $node->type = $typeNode;
            return $node;
        }
        return $node;
    }
}
