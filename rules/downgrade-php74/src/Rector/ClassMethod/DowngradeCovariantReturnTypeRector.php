<?php

declare(strict_types=1);

namespace Rector\DowngradePhp74\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\UnionType;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://www.php.net/manual/en/migration74.new-features.php#migration74.new-features.core.type-variance
 *
 * @see \Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\DowngradeCovariantReturnTypeRectorTest
 */
final class DowngradeCovariantReturnTypeRector extends AbstractRector
{
    /**
     * @var PhpDocTypeChanger
     */
    private $phpDocTypeChanger;

    public function __construct(PhpDocTypeChanger $phpDocTypeChanger)
    {
        $this->phpDocTypeChanger = $phpDocTypeChanger;
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
    { /* … */ }
}

class B extends A
{
    public function covariantReturnTypes(): ChildType
    { /* … */ }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class ParentType {}
class ChildType extends ParentType {}

class A
{
    public function covariantReturnTypes(): ParentType
    { /* … */ }
}

class B extends A
{
    /**
     * @return ChildType
     */
    public function covariantReturnTypes(): ParentType
    { /* … */ }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
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
        if (! $this->shouldRefactor($node)) {
            return null;
        }

        /** @var string $parentReflectionMethodName */
        $parentReflectionMethodName = $this->getDifferentReturnTypeNameFromAncestorClass($node);
        // The return type name could either be a classname, without the leading "\",
        // or one among the reserved identifiers ("static", "self", "iterable", etc)
        // To find out which is the case, check if this name exists as a class
        $newType = ClassExistenceStaticHelper::doesClassLikeExist($parentReflectionMethodName) ? new FullyQualified(
            $parentReflectionMethodName
        ) : new Name($parentReflectionMethodName);

        // Make it nullable?
        if ($node->returnType instanceof NullableType) {
            $newType = new NullableType($newType);
        }

        // Add the docblock before changing the type
        $this->addDocBlockReturn($node);

        $node->returnType = $newType;

        return $node;
    }

    private function shouldRefactor(ClassMethod $classMethod): bool
    {
        return $this->getDifferentReturnTypeNameFromAncestorClass($classMethod) !== null;
    }

    private function getDifferentReturnTypeNameFromAncestorClass(ClassMethod $classMethod): ?string
    {
        $scope = $classMethod->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            // possibly trait
            return null;
        }

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return null;
        }

        $nodeReturnType = $classMethod->returnType;
        if ($nodeReturnType === null) {
            return null;
        }
        if ($nodeReturnType instanceof UnionType) {
            return null;
        }
        $nodeReturnTypeName = $this->getName(
            $nodeReturnType instanceof NullableType ? $nodeReturnType->type : $nodeReturnType
        );

        /** @var string $methodName */
        $methodName = $this->getName($classMethod->name);

        // Either Ancestor classes or implemented interfaces
        $interfaceName = array_map(
            function (ClassReflection $interfaceReflection): string {
                return $interfaceReflection->getName();
            },
            $classReflection->getInterfaces()
        );

        $parentClassesNames = $classReflection->getParentClassesNames();

        $parentClassLikes = array_merge($parentClassesNames, $interfaceName);

        foreach ($parentClassLikes as $parentClassLike) {
            if (! method_exists($parentClassLike, $methodName)) {
                continue;
            }

            $parentReflectionMethod = new ReflectionMethod($parentClassLike, $methodName);
            $parentReflectionMethodReturnType = $parentReflectionMethod->getReturnType();

            if ($this->isNotReflectionNamedTypeOrNotEqualsToNodeReturnTypeName(
                $parentReflectionMethodReturnType,
                $nodeReturnTypeName
            )) {
                continue;
            }

            // This is an ancestor class with a different return type
            /** @var ReflectionNamedType $parentReflectionMethodReturnType */
            return $parentReflectionMethodReturnType->getName();
        }

        return null;
    }

    private function isNotReflectionNamedTypeOrNotEqualsToNodeReturnTypeName(
        ?ReflectionType $reflectionType,
        ?string $nodeReturnTypeName
    ): bool {
        if (! $reflectionType instanceof ReflectionNamedType) {
            return true;
        }

        return $reflectionType->getName() === $nodeReturnTypeName;
    }

    private function addDocBlockReturn(ClassMethod $classMethod): void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);

        /** @var Node $returnType */
        $returnType = $classMethod->returnType;
        $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($returnType);

        $this->phpDocTypeChanger->changeReturnType($phpDocInfo, $type);
    }
}
