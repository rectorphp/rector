<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Doctrine\NodeManipulator\ColumnPropertyTypeResolver;
use Rector\Doctrine\NodeManipulator\NullabilityColumnPropertyTypeResolver;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\TypeDeclaration\NodeTypeAnalyzer\PropertyTypeDecorator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\Rector\Property\TypedPropertyFromColumnTypeRector\TypedPropertyFromColumnTypeRectorTest
 */
final class TypedPropertyFromColumnTypeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeTypeAnalyzer\PropertyTypeDecorator
     */
    private $propertyTypeDecorator;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeManipulator\ColumnPropertyTypeResolver
     */
    private $columnPropertyTypeResolver;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeManipulator\NullabilityColumnPropertyTypeResolver
     */
    private $nullabilityColumnPropertyTypeResolver;
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(PropertyTypeDecorator $propertyTypeDecorator, ColumnPropertyTypeResolver $columnPropertyTypeResolver, PhpDocTypeChanger $phpDocTypeChanger, NullabilityColumnPropertyTypeResolver $nullabilityColumnPropertyTypeResolver, PhpVersionProvider $phpVersionProvider)
    {
        $this->propertyTypeDecorator = $propertyTypeDecorator;
        $this->columnPropertyTypeResolver = $columnPropertyTypeResolver;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->nullabilityColumnPropertyTypeResolver = $nullabilityColumnPropertyTypeResolver;
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Complete @var annotations or types based on @ORM\\Column', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

class SimpleColumn
{
    /**
     * @ORM\Column(type="string")
     */
    private $name;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

class SimpleColumn
{
    /**
     * @ORM\Column(type="string")
     */
    private string|null $name = null;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Property::class];
    }
    /**
     * @param Property $node
     * @return \PhpParser\Node\Stmt\Property|null
     */
    public function refactor(Node $node)
    {
        if ($node->type !== null) {
            return null;
        }
        $isNullable = $this->nullabilityColumnPropertyTypeResolver->isNullable($node);
        $propertyType = $this->columnPropertyTypeResolver->resolve($node, $isNullable);
        if (!$propertyType instanceof Type || $propertyType instanceof MixedType) {
            return null;
        }
        // add default null if missing
        if ($isNullable && !TypeCombinator::containsNull($propertyType)) {
            $propertyType = TypeCombinator::addNull($propertyType);
        }
        $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType, TypeKind::PROPERTY);
        if ($typeNode === null) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersion::PHP_74)) {
            if ($propertyType instanceof UnionType) {
                $this->propertyTypeDecorator->decoratePropertyUnionType($propertyType, $typeNode, $node, $phpDocInfo);
                return $node;
            }
            $node->type = $typeNode;
            return $node;
        }
        $this->phpDocTypeChanger->changeVarType($phpDocInfo, $propertyType);
        return $node;
    }
}
