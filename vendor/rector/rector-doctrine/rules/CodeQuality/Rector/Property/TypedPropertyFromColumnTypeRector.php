<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\Rector\Property;

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
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Doctrine\NodeManipulator\ColumnPropertyTypeResolver;
use Rector\Doctrine\NodeManipulator\NullabilityColumnPropertyTypeResolver;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\TypeDeclaration\NodeTypeAnalyzer\PropertyTypeDecorator;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromColumnTypeRector\TypedPropertyFromColumnTypeRectorTest
 */
final class TypedPropertyFromColumnTypeRector extends AbstractRector implements MinPhpVersionInterface
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
     * @var \Rector\Doctrine\NodeManipulator\NullabilityColumnPropertyTypeResolver
     */
    private $nullabilityColumnPropertyTypeResolver;
    public function __construct(PropertyTypeDecorator $propertyTypeDecorator, ColumnPropertyTypeResolver $columnPropertyTypeResolver, NullabilityColumnPropertyTypeResolver $nullabilityColumnPropertyTypeResolver)
    {
        $this->propertyTypeDecorator = $propertyTypeDecorator;
        $this->columnPropertyTypeResolver = $columnPropertyTypeResolver;
        $this->nullabilityColumnPropertyTypeResolver = $nullabilityColumnPropertyTypeResolver;
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
     */
    public function refactor(Node $node) : ?\PhpParser\Node\Stmt\Property
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
        if ($propertyType instanceof UnionType) {
            $this->propertyTypeDecorator->decoratePropertyUnionType($propertyType, $typeNode, $node, $phpDocInfo);
            return $node;
        }
        $node->type = $typeNode;
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }
}
