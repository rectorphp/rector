<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover;
use Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\ConstructorPropertyTypeInferer;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\TypedPropertyFromStrictConstructorRectorTest
 */
final class TypedPropertyFromStrictConstructorRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @var \Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\ConstructorPropertyTypeInferer
     */
    private $constructorPropertyTypeInferer;
    /**
     * @var \Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover
     */
    private $varTagRemover;
    public function __construct(\Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\ConstructorPropertyTypeInferer $constructorPropertyTypeInferer, \Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover $varTagRemover)
    {
        $this->constructorPropertyTypeInferer = $constructorPropertyTypeInferer;
        $this->varTagRemover = $varTagRemover;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add typed properties based only on strict constructor types', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeObject
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeObject
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
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
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node->type !== null) {
            return null;
        }
        $varType = $this->constructorPropertyTypeInferer->inferProperty($node);
        if (!$varType instanceof \PHPStan\Type\Type) {
            return null;
        }
        if ($varType instanceof \PHPStan\Type\MixedType) {
            return null;
        }
        $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($varType, \Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind::PROPERTY());
        if (!$propertyTypeNode instanceof \PhpParser\Node) {
            return null;
        }
        $node->type = $propertyTypeNode;
        $node->props[0]->default = null;
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $this->varTagRemover->removeVarTagIfUseless($phpDocInfo, $node);
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::TYPED_PROPERTIES;
    }
}
