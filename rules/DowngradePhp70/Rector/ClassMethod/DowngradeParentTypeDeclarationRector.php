<?php

declare(strict_types=1);

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
final class DowngradeParentTypeDeclarationRector extends AbstractRector
{
    public function __construct(
        private PhpDocTypeChanger $phpDocTypeChanger,
        private ReflectionProvider $reflectionProvider
    ) {
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove "parent" return type, add a "@return parent" tag instead',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
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
                    ,
                    <<<'CODE_SAMPLE'
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
                ),
            ]
        );
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->returnType instanceof Name) {
            return null;
        }

        if (! $this->nodeNameResolver->isName($node->returnType, 'parent')) {
            return null;
        }

        $node->returnType = null;

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if ($phpDocInfo->hasByType(ReturnTagValueNode::class)) {
            return $node;
        }

        $parentStaticType = $this->getType($node);

        if (! $parentStaticType instanceof ParentStaticType) {
            return $node;
        }

        $this->phpDocTypeChanger->changeReturnType($phpDocInfo, $parentStaticType);
        return $node;
    }

    private function getType(ClassMethod $classMethod): ?ParentStaticType
    {
        $scope = $classMethod->getAttribute(AttributeKey::SCOPE);
        if ($scope === null) {
            // in a trait
            $className = $classMethod->getAttribute(AttributeKey::CLASS_NAME);
            $classReflection = $this->reflectionProvider->getClass($className);
        } else {
            $classReflection = $scope->getClassReflection();
        }

        if (! $classReflection instanceof ClassReflection) {
            return null;
        }

        return new ParentStaticType($classReflection);
    }
}
