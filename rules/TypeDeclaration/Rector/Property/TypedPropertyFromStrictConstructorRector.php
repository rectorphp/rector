<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\ConstructorPropertyTypeInferer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\TypedPropertyFromStrictConstructorRectorTest
 */
final class TypedPropertyFromStrictConstructorRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\ConstructorPropertyTypeInferer
     */
    private $constructorPropertyTypeInferer;
    /**
     * @readonly
     * @var \Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover
     */
    private $varTagRemover;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector
     */
    private $constructorAssignDetector;
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(\Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\ConstructorPropertyTypeInferer $constructorPropertyTypeInferer, \Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover $varTagRemover, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger $phpDocTypeChanger, \Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector $constructorAssignDetector, \Rector\Core\Php\PhpVersionProvider $phpVersionProvider)
    {
        $this->constructorPropertyTypeInferer = $constructorPropertyTypeInferer;
        $this->varTagRemover = $varTagRemover;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->constructorAssignDetector = $constructorAssignDetector;
        $this->phpVersionProvider = $phpVersionProvider;
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
        $classLike = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\Class_::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::TYPED_PROPERTIES)) {
            $this->phpDocTypeChanger->changeVarType($phpDocInfo, $varType);
            return $node;
        }
        $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($varType, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::PROPERTY());
        if (!$propertyTypeNode instanceof \PhpParser\Node) {
            return null;
        }
        // public property can be anything
        if ($node->isPublic()) {
            $this->phpDocTypeChanger->changeVarType($phpDocInfo, $varType);
            return $node;
        }
        $node->type = $propertyTypeNode;
        $propertyName = $this->nodeNameResolver->getName($node);
        if ($this->constructorAssignDetector->isPropertyAssigned($classLike, $propertyName)) {
            $node->props[0]->default = null;
        }
        $this->varTagRemover->removeVarTagIfUseless($phpDocInfo, $node);
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::TYPED_PROPERTIES;
    }
}
