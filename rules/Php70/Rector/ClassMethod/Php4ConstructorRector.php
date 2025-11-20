<?php

declare (strict_types=1);
namespace Rector\Php70\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Enum\ObjectReference;
use Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver;
use Rector\Php70\NodeAnalyzer\MethodCallNameAnalyzer;
use Rector\Php70\NodeAnalyzer\Php4ConstructorClassMethodAnalyzer;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php70\Rector\ClassMethod\Php4ConstructorRector\Php4ConstructorRectorTest
 */
final class Php4ConstructorRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private Php4ConstructorClassMethodAnalyzer $php4ConstructorClassMethodAnalyzer;
    /**
     * @readonly
     */
    private ParentClassScopeResolver $parentClassScopeResolver;
    /**
     * @readonly
     */
    private MethodCallNameAnalyzer $methodCallNameAnalyzer;
    public function __construct(Php4ConstructorClassMethodAnalyzer $php4ConstructorClassMethodAnalyzer, ParentClassScopeResolver $parentClassScopeResolver, MethodCallNameAnalyzer $methodCallNameAnalyzer)
    {
        $this->php4ConstructorClassMethodAnalyzer = $php4ConstructorClassMethodAnalyzer;
        $this->parentClassScopeResolver = $parentClassScopeResolver;
        $this->methodCallNameAnalyzer = $methodCallNameAnalyzer;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::NO_PHP4_CONSTRUCTOR;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change PHP 4 style constructor to `__construct`', [new CodeSample(<<<'CODE_SAMPLE'
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
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?\PhpParser\Node\Stmt\Class_
    {
        $scope = ScopeFetcher::fetch($node);
        // catch only classes without namespace
        if ($scope->getNamespace() !== null) {
            return null;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        $className = $this->getName($node);
        if (!is_string($className)) {
            return null;
        }
        foreach ($node->stmts as $classStmtKey => $classStmt) {
            if (!$classStmt instanceof ClassMethod) {
                continue;
            }
            if (!$this->php4ConstructorClassMethodAnalyzer->detect($classStmt, $classReflection)) {
                continue;
            }
            $psr4ConstructorMethod = $classStmt;
            // process parent call references first
            $this->processClassMethodStatementsForParentConstructorCalls($psr4ConstructorMethod, $scope);
            // does it already have a __construct method?
            if (!$node->getMethod(MethodName::CONSTRUCT) instanceof ClassMethod) {
                $psr4ConstructorMethod->name = new Identifier(MethodName::CONSTRUCT);
            }
            foreach ((array) $psr4ConstructorMethod->stmts as $classMethodStmt) {
                if (!$classMethodStmt instanceof Expression) {
                    continue;
                }
                // remove delegating method
                if ($this->methodCallNameAnalyzer->isLocalMethodCallNamed($classMethodStmt->expr, MethodName::CONSTRUCT)) {
                    unset($node->stmts[$classStmtKey]);
                }
                if ($this->methodCallNameAnalyzer->isParentMethodCall($node, $classMethodStmt->expr)) {
                    /** @var MethodCall $expr */
                    $expr = $classMethodStmt->expr;
                    /** @var string $parentClassName */
                    $parentClassName = $this->getParentClassName($node);
                    $classMethodStmt->expr = new StaticCall(new FullyQualified($parentClassName), new Identifier(MethodName::CONSTRUCT), $expr->args);
                }
            }
            return $node;
        }
        return null;
    }
    private function processClassMethodStatementsForParentConstructorCalls(ClassMethod $classMethod, Scope $scope): void
    {
        foreach ((array) $classMethod->stmts as $methodStmt) {
            if (!$methodStmt instanceof Expression) {
                continue;
            }
            $methodStmt = $methodStmt->expr;
            if (!$methodStmt instanceof StaticCall) {
                continue;
            }
            $this->processParentPhp4ConstructCall($methodStmt, $scope);
        }
    }
    private function processParentPhp4ConstructCall(StaticCall $staticCall, Scope $scope): void
    {
        $parentClassReflection = $this->parentClassScopeResolver->resolveParentClassReflection($scope);
        // no parent class
        if (!$parentClassReflection instanceof ClassReflection) {
            return;
        }
        if (!$staticCall->class instanceof Name) {
            return;
        }
        // rename ParentClass
        if ($this->isName($staticCall->class, $parentClassReflection->getName())) {
            $staticCall->class = new Name(ObjectReference::PARENT);
        }
        if (!$this->isName($staticCall->class, ObjectReference::PARENT)) {
            return;
        }
        // it's not a parent PHP 4 constructor call
        if (!$this->isName($staticCall->name, $parentClassReflection->getName())) {
            return;
        }
        $staticCall->name = new Identifier(MethodName::CONSTRUCT);
    }
    private function getParentClassName(Class_ $class): ?string
    {
        if (!$class->extends instanceof Node) {
            return null;
        }
        return $class->extends->toString();
    }
}
