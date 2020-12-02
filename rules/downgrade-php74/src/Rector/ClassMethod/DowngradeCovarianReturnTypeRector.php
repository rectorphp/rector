<?php

declare(strict_types=1);

namespace Rector\DowngradePhp74\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\UnionType;
use PHPStan\Analyser\Scope;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionMethod;
use ReflectionNamedType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://www.php.net/manual/en/migration74.new-features.php#migration74.new-features.core.type-variance
 *
 * @see \Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovarianReturnTypeRector\DowngradeCovarianReturnTypeRectorTest
 */
final class DowngradeCovarianReturnTypeRector extends AbstractRector
{
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

        /** @var string */
        $parentReflectionMethodClassname = $this->getDifferentReturnTypeClassnameFromAncestorClass($node);
        $newType = new FullyQualified($parentReflectionMethodClassname);

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
        return $this->getDifferentReturnTypeClassnameFromAncestorClass($classMethod) !== null;
    }

    private function getDifferentReturnTypeClassnameFromAncestorClass(ClassMethod $classMethod): ?string
    {
        /** @var Scope|null $scope */
        $scope = $classMethod->getAttribute(AttributeKey::SCOPE);
        if ($scope === null) {
            // possibly trait
            return null;
        }

        $classReflection = $scope->getClassReflection();
        if ($classReflection === null) {
            return null;
        }

        $nodeReturnType = $classMethod->returnType;
        if ($nodeReturnType === null || $nodeReturnType instanceof UnionType) {
            return null;
        }
        $nodeReturnTypeName = $this->getName($nodeReturnType);

        /** @var string $methodName */
        $methodName = $this->getName($classMethod->name);

        foreach ($classReflection->getParentClassesNames() as $parentClassName) {
            if (! method_exists($parentClassName, $methodName)) {
                continue;
            }

            $parentReflectionMethod = new ReflectionMethod($parentClassName, $methodName);
            /** @var ReflectionNamedType|null */
            $parentReflectionMethodReturnType = $parentReflectionMethod->getReturnType();
            if ($parentReflectionMethodReturnType === null || $parentReflectionMethodReturnType->getName() === $nodeReturnTypeName) {
                continue;
            }

            // This is an ancestor class with a different return type
            return $parentReflectionMethodReturnType->getName();
        }

        return null;
    }

    private function addDocBlockReturn(ClassMethod $classMethod): void
    {
        /** @var PhpDocInfo|null */
        $phpDocInfo = $classMethod->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            $phpDocInfo = $this->phpDocInfoFactory->createEmpty($classMethod);
        }

        /** @var Node */
        $returnType = $classMethod->returnType;
        $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($returnType);
        $phpDocInfo->changeReturnType($type);
    }
}
