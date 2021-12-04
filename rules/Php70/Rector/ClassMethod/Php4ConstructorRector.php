<?php

declare (strict_types=1);
namespace Rector\Php70\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php70\NodeAnalyzer\Php4ConstructorClassMethodAnalyzer;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/remove_php4_constructors
 * @see \Rector\Tests\Php70\Rector\ClassMethod\Php4ConstructorRector\Php4ConstructorRectorTest
 */
final class Php4ConstructorRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Php70\NodeAnalyzer\Php4ConstructorClassMethodAnalyzer
     */
    private $php4ConstructorClassMethodAnalyzer;
    /**
     * @readonly
     * @var \Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver
     */
    private $parentClassScopeResolver;
    public function __construct(\Rector\Php70\NodeAnalyzer\Php4ConstructorClassMethodAnalyzer $php4ConstructorClassMethodAnalyzer, \Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver $parentClassScopeResolver)
    {
        $this->php4ConstructorClassMethodAnalyzer = $php4ConstructorClassMethodAnalyzer;
        $this->parentClassScopeResolver = $parentClassScopeResolver;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::NO_PHP4_CONSTRUCTOR;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes PHP 4 style constructor to __construct.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function SomeClass()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct()
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
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->php4ConstructorClassMethodAnalyzer->detect($node)) {
            return null;
        }
        $classLike = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\Class_::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return null;
        }
        // process parent call references first
        $this->processClassMethodStatementsForParentConstructorCalls($node);
        // not PSR-4 constructor
        if (!$this->nodeNameResolver->areNamesEqual($classLike, $node)) {
            return null;
        }
        $classMethod = $classLike->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        // does it already have a __construct method?
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            $node->name = new \PhpParser\Node\Identifier(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        }
        $stmts = $node->stmts;
        if ($stmts === null) {
            return null;
        }
        if (\count($stmts) === 1) {
            /** @var Expression|Expr $stmt */
            $stmt = $stmts[0];
            if (!$stmt instanceof \PhpParser\Node\Stmt\Expression) {
                return null;
            }
            if ($this->isLocalMethodCallNamed($stmt->expr, \Rector\Core\ValueObject\MethodName::CONSTRUCT)) {
                $this->removeNode($node);
                return null;
            }
        }
        return $node;
    }
    private function processClassMethodStatementsForParentConstructorCalls(\PhpParser\Node\Stmt\ClassMethod $classMethod) : void
    {
        if (!\is_iterable($classMethod->stmts)) {
            return;
        }
        foreach ($classMethod->stmts as $methodStmt) {
            if (!$methodStmt instanceof \PhpParser\Node\Stmt\Expression) {
                continue;
            }
            $methodStmt = $methodStmt->expr;
            if (!$methodStmt instanceof \PhpParser\Node\Expr\StaticCall) {
                continue;
            }
            $this->processParentPhp4ConstructCall($methodStmt);
        }
    }
    private function processParentPhp4ConstructCall(\PhpParser\Node\Expr\StaticCall $staticCall) : void
    {
        $scope = $staticCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        $parentClassReflection = $this->parentClassScopeResolver->resolveParentClassReflection($scope);
        // no parent class
        if (!$parentClassReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return;
        }
        if (!$staticCall->class instanceof \PhpParser\Node\Name) {
            return;
        }
        // rename ParentClass
        if ($this->isName($staticCall->class, $parentClassReflection->getName())) {
            $staticCall->class = new \PhpParser\Node\Name(\Rector\Core\Enum\ObjectReference::PARENT()->getValue());
        }
        if (!$this->isName($staticCall->class, \Rector\Core\Enum\ObjectReference::PARENT()->getValue())) {
            return;
        }
        // it's not a parent PHP 4 constructor call
        if (!$this->isName($staticCall->name, $parentClassReflection->getName())) {
            return;
        }
        $staticCall->name = new \PhpParser\Node\Identifier(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
    }
    private function isLocalMethodCallNamed(\PhpParser\Node\Expr $expr, string $name) : bool
    {
        if (!$expr instanceof \PhpParser\Node\Expr\MethodCall) {
            return \false;
        }
        if ($expr->var instanceof \PhpParser\Node\Expr\StaticCall) {
            return \false;
        }
        if ($expr->var instanceof \PhpParser\Node\Expr\MethodCall) {
            return \false;
        }
        if (!$this->isName($expr->var, 'this')) {
            return \false;
        }
        return $this->isName($expr->name, $name);
    }
}
