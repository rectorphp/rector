<?php

declare(strict_types=1);

namespace Rector\DowngradePhp74\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\UnionType;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\PhpDoc\TagRemover\ReturnTagRemover;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://www.php.net/manual/en/migration74.new-features.php#migration74.new-features.core.type-variance
 *
 * @see \Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\DowngradeCovariantReturnTypeRectorTest
 */
final class DowngradeCovariantReturnTypeRector extends AbstractRector
{
    public function __construct(
        private PhpDocTypeChanger $phpDocTypeChanger,
        private PrivatesCaller $privatesCaller,
        private ReturnTagRemover $returnTagRemover
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Make method return same type as parent', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class ParentType {}
class ChildType extends ParentType {}

class A
{
    public function covariantReturnTypes(): ParentType
    {
    }
}

class B extends A
{
    public function covariantReturnTypes(): ChildType
    {
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class ParentType {}
class ChildType extends ParentType {}

class A
{
    public function covariantReturnTypes(): ParentType
    {
    }
}

class B extends A
{
    /**
     * @return ChildType
     */
    public function covariantReturnTypes(): ParentType
    {
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->returnType === null) {
            return null;
        }

        $parentReturnType = $this->resolveDifferentAncestorReturnType($node, $node->returnType);
        if ($parentReturnType instanceof MixedType) {
            return null;
        }

        // The return type name could either be a classname, without the leading "\",
        // or one among the reserved identifiers ("static", "self", "iterable", etc)
        // To find out which is the case, check if this name exists as a class
        $parentReturnTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($parentReturnType);
        if (! $parentReturnTypeNode instanceof Node) {
            return null;
        }

        // Make it nullable?
        if ($node->returnType instanceof NullableType && ! $parentReturnTypeNode instanceof NullableType && ! $parentReturnTypeNode instanceof UnionType) {
            $parentReturnTypeNode = new NullableType($parentReturnTypeNode);
        }

        // skip if type is already set
        if ($this->nodeComparator->areNodesEqual($parentReturnTypeNode, $node->returnType)) {
            return null;
        }

        // Add the docblock before changing the type
        $this->addDocBlockReturn($node);
        $node->returnType = $parentReturnTypeNode;

        return $node;
    }

    /**
     * @param UnionType|NullableType|Name|Node\Identifier $returnTypeNode
     */
    private function resolveDifferentAncestorReturnType(ClassMethod $classMethod, Node $returnTypeNode): Type
    {
        $scope = $classMethod->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            // possibly trait
            return new MixedType();
        }

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return new MixedType();
        }

        if ($returnTypeNode instanceof UnionType) {
            return new MixedType();
        }

        $bareReturnType = $returnTypeNode instanceof NullableType ? $returnTypeNode->type : $returnTypeNode;

        $returnType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($bareReturnType);
        $methodName = $this->getName($classMethod);

        /** @var ClassReflection[] $parentClassesAndInterfaces */
        $parentClassesAndInterfaces = array_merge($classReflection->getParents(), $classReflection->getInterfaces());

        foreach ($parentClassesAndInterfaces as $parentClassAndInterface) {
            $parentClassAndInterfaceHasMethod = $parentClassAndInterface->hasMethod($methodName);
            if (! $parentClassAndInterfaceHasMethod) {
                continue;
            }

            $classMethodScope = $classMethod->getAttribute(AttributeKey::SCOPE);
            $parameterMethodReflection = $parentClassAndInterface->getMethod($methodName, $classMethodScope);

            if (! $parameterMethodReflection instanceof PhpMethodReflection) {
                continue;
            }

            /** @var Type $parentReturnType */
            $parentReturnType = $this->privatesCaller->callPrivateMethod(
                $parameterMethodReflection,
                'getReturnType',
                []
            );

            if ($parentReturnType->equals($returnType)) {
                continue;
            }

            // This is an ancestor class with a different return type
            return $parentReturnType;
        }

        return new MixedType();
    }

    private function addDocBlockReturn(ClassMethod $classMethod): void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);

        // keep return type if already set one
        if (! $phpDocInfo->getReturnType() instanceof MixedType) {
            return;
        }

        /** @var Node $returnType */
        $returnType = $classMethod->returnType;
        $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($returnType);

        $this->phpDocTypeChanger->changeReturnType($phpDocInfo, $type);
        $this->returnTagRemover->removeReturnTagIfUseless($phpDocInfo, $classMethod);
    }
}
