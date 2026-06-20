<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202606\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\DeadCode\Rector\Class_\RemoveRefactorDuplicatedNodeInstanceCheckRector\RemoveRefactorDuplicatedNodeInstanceCheckRectorTest
 */
final class RemoveRefactorDuplicatedNodeInstanceCheckRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, ValueResolver $valueResolver)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove refactor() method of Rector rule double check of $classMethod instance, if already defined in @param type', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node)
    {
        if (! $node instanceof ClassMethod) {
            return null;
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node)
    {
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $scope = ScopeFetcher::fetch($node);
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        if (!$classReflection->is('Rector\Rector\AbstractRector')) {
            return null;
        }
        $refactorClassMethod = $node->getMethod('refactor');
        if (!$refactorClassMethod instanceof ClassMethod) {
            return null;
        }
        $firstStmt = $refactorClassMethod->stmts[0] ?? null;
        if (!$firstStmt instanceof If_) {
            return null;
        }
        $instanceofNodeClass = $this->matchBooleanNotInstanceOfNodeClass($firstStmt->cond);
        if (!is_string($instanceofNodeClass)) {
            return null;
        }
        $nodeParamTypeClass = $this->matchNodeParamType($refactorClassMethod);
        $getNodeTypesClassMethod = $node->getMethod('getNodeTypes');
        if (!$getNodeTypesClassMethod instanceof ClassMethod) {
            return null;
        }
        $soleReturn = $getNodeTypesClassMethod->stmts[0] ?? null;
        $nodeTypeClass = null;
        if ($soleReturn instanceof Return_) {
            Assert::isInstanceOf($soleReturn->expr, Expr::class);
            $nodeTypes = $this->valueResolver->getValue($soleReturn->expr);
            if (count($nodeTypes) === 1) {
                $nodeTypeClass = $nodeTypes[0];
            }
        }
        if ($nodeParamTypeClass !== null) {
            if ($nodeParamTypeClass !== $instanceofNodeClass) {
                return null;
            }
        } elseif ($nodeTypeClass !== null) {
            if ($nodeTypeClass !== $instanceofNodeClass) {
                return null;
            }
        } else {
            return null;
        }
        unset($refactorClassMethod->stmts[0]);
        return $node;
    }
    private function matchBooleanNotInstanceOfNodeClass(Expr $expr): ?string
    {
        if (!$expr instanceof BooleanNot) {
            return null;
        }
        $booleanNot = $expr;
        if (!$booleanNot->expr instanceof Instanceof_) {
            return null;
        }
        return $this->getInstanceofNodeClass($booleanNot->expr);
    }
    /**
     * @return class-string<Node>|null
     */
    private function getInstanceofNodeClass(Instanceof_ $instanceof): ?string
    {
        $checkedClassType = $this->getType($instanceof->class);
        if (!$checkedClassType instanceof ObjectType) {
            return null;
        }
        /** @var ClassReflection $classReflection */
        $classReflection = $checkedClassType->getClassReflection();
        if (!$classReflection->is(Node::class)) {
            return null;
        }
        return $classReflection->getName();
    }
    private function matchNodeParamType(ClassMethod $classMethod): ?string
    {
        $classMethodPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $paramType = $classMethodPhpDocInfo->getParamType('$node');
        if (!$paramType instanceof ObjectType) {
            return null;
        }
        if ($paramType instanceof ShortenedObjectType) {
            return $paramType->getFullyQualifiedName();
        }
        return $paramType->getClassName();
    }
}
