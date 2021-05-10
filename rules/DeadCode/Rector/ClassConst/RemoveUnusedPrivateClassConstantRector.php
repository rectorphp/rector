<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use Rector\Core\NodeManipulator\ClassConstManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassConst\RemoveUnusedPrivateClassConstantRector\RemoveUnusedPrivateClassConstantRectorTest
 */
final class RemoveUnusedPrivateClassConstantRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var ClassConstManipulator
     */
    private $classConstManipulator;
    public function __construct(\Rector\Core\NodeManipulator\ClassConstManipulator $classConstManipulator)
    {
        $this->classConstManipulator = $classConstManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove unused class constants', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    private const SOME_CONST = 'dead';

    public function run()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
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
        return [\PhpParser\Node\Stmt\ClassConst::class];
    }
    /**
     * @param ClassConst $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return null;
        }
        $classLike = $this->nodeRepository->findClassLike($classReflection->getName());
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return null;
        }
        $classObjectType = new \PHPStan\Type\ObjectType($classReflection->getName());
        /** @var ClassConstFetch[] $classConstFetches */
        $classConstFetches = $this->betterNodeFinder->findInstanceOf($classLike->stmts, \PhpParser\Node\Expr\ClassConstFetch::class);
        foreach ($classConstFetches as $classConstFetch) {
            if (!$this->nodeNameResolver->areNamesEqual($classConstFetch->name, $node->consts[0]->name)) {
                continue;
            }
            $constFetchClassType = $this->nodeTypeResolver->resolve($classConstFetch->class);
            // constant is used!
            if ($constFetchClassType->isSuperTypeOf($classObjectType)->yes()) {
                return null;
            }
        }
        $this->removeNode($node);
        return null;
    }
    private function shouldSkip(\PhpParser\Node\Stmt\ClassConst $classConst) : bool
    {
        if (!$classConst->isPrivate()) {
            return \true;
        }
        if (\count($classConst->consts) !== 1) {
            return \true;
        }
        if ($this->classConstManipulator->isEnum($classConst)) {
            return \true;
        }
        if ($this->classConstManipulator->hasClassConstFetch($classConst)) {
            return \true;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classConst);
        if ($phpDocInfo->hasByName('api')) {
            return \true;
        }
        $classLike = $classConst->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if ($classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classLike);
            return $phpDocInfo->hasByName('api');
        }
        return \false;
    }
}
