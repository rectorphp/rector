<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector;
use Rector\TypeDeclaration\Guard\PropertyTypeOverrideGuard;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\TrustedClassMethodPropertyTypeInferer;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\TypedPropertyFromStrictConstructorRectorTest
 */
final class TypedPropertyFromStrictConstructorRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\TrustedClassMethodPropertyTypeInferer
     */
    private $trustedClassMethodPropertyTypeInferer;
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
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\Guard\PropertyTypeOverrideGuard
     */
    private $propertyTypeOverrideGuard;
    public function __construct(TrustedClassMethodPropertyTypeInferer $trustedClassMethodPropertyTypeInferer, VarTagRemover $varTagRemover, PhpDocTypeChanger $phpDocTypeChanger, ConstructorAssignDetector $constructorAssignDetector, PhpVersionProvider $phpVersionProvider, PropertyTypeOverrideGuard $propertyTypeOverrideGuard)
    {
        $this->trustedClassMethodPropertyTypeInferer = $trustedClassMethodPropertyTypeInferer;
        $this->varTagRemover = $varTagRemover;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->constructorAssignDetector = $constructorAssignDetector;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->propertyTypeOverrideGuard = $propertyTypeOverrideGuard;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add typed properties based only on strict constructor types', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $hasChanged = \false;
        $constructClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof ClassMethod) {
            return null;
        }
        foreach ($node->getProperties() as $property) {
            $propertyType = $this->trustedClassMethodPropertyTypeInferer->inferProperty($property, $constructClassMethod);
            if ($propertyType instanceof MixedType) {
                continue;
            }
            if ($propertyType instanceof ObjectType && $propertyType->isInstanceOf('Doctrine\\Common\\Collections\\Collection')->yes()) {
                continue;
            }
            if (!$this->propertyTypeOverrideGuard->isLegal($property)) {
                continue;
            }
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
            // public property can be anything
            if ($this->isVarDocPreffered($property)) {
                $this->phpDocTypeChanger->changeVarType($phpDocInfo, $propertyType);
                $hasChanged = \true;
                continue;
            }
            $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType, TypeKind::PROPERTY);
            if (!$propertyTypeNode instanceof Node) {
                continue;
            }
            if (!$property->isPublic()) {
                $property->type = $propertyTypeNode;
            }
            $propertyName = $this->nodeNameResolver->getName($property);
            if ($this->constructorAssignDetector->isPropertyAssigned($node, $propertyName)) {
                $property->props[0]->default = null;
            }
            $this->varTagRemover->removeVarTagIfUseless($phpDocInfo, $property);
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }
    private function isVarDocPreffered(Property $property) : bool
    {
        if ($property->isPublic()) {
            return \true;
        }
        return !$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::TYPED_PROPERTIES);
    }
}
