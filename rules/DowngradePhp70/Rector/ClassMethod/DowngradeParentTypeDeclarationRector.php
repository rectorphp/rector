<?php

declare (strict_types=1);
namespace Rector\DowngradePhp70\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\ValueObject\Type\ParentStaticType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp70\Rector\ClassMethod\DowngradeParentTypeDeclarationRector\DowngradeParentTypeDeclarationRectorTest
 */
final class DowngradeParentTypeDeclarationRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger $phpDocTypeChanger, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove "parent" return type, add a "@return parent" tag instead', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class ParentClass
{
}

class SomeClass extends ParentClass
{
    public function foo(): parent
    {
        return $this;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class ParentClass
{
}

class SomeClass extends ParentClass
{
    /**
     * @return parent
     */
    public function foo()
    {
        return $this;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$node->returnType instanceof \PhpParser\Node\Name) {
            return null;
        }
        if (!$this->nodeNameResolver->isName($node->returnType, 'parent')) {
            return null;
        }
        $node->returnType = null;
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if ($phpDocInfo->hasByType(\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode::class)) {
            return $node;
        }
        $parentStaticType = $this->getType($node);
        if (!$parentStaticType instanceof \Rector\StaticTypeMapper\ValueObject\Type\ParentStaticType) {
            return $node;
        }
        $this->phpDocTypeChanger->changeReturnType($phpDocInfo, $parentStaticType);
        return $node;
    }
    private function getType(\PhpParser\Node\Stmt\ClassMethod $classMethod) : ?\Rector\StaticTypeMapper\ValueObject\Type\ParentStaticType
    {
        $scope = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if ($scope === null) {
            // in a trait
            $className = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
            $classReflection = $this->reflectionProvider->getClass($className);
        } else {
            $classReflection = $scope->getClassReflection();
        }
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return null;
        }
        return new \Rector\StaticTypeMapper\ValueObject\Type\ParentStaticType($classReflection);
    }
}
