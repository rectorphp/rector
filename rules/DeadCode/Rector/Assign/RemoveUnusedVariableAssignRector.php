<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeVisitor;
use PHPStan\Reflection\AttributeReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\DeadCode\SideEffect\SideEffectNodeDetector;
use Rector\NodeAnalyzer\VariableAnalyzer;
use Rector\NodeManipulator\StmtsManipulator;
use Rector\Php\ReservedKeywordAnalyzer;
use Rector\PhpParser\Enum\NodeGroup;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\RemoveUnusedVariableAssignRectorTest
 */
final class RemoveUnusedVariableAssignRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ReservedKeywordAnalyzer $reservedKeywordAnalyzer;
    /**
     * @readonly
     */
    private SideEffectNodeDetector $sideEffectNodeDetector;
    /**
     * @readonly
     */
    private VariableAnalyzer $variableAnalyzer;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private StmtsManipulator $stmtsManipulator;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    public function __construct(ReservedKeywordAnalyzer $reservedKeywordAnalyzer, SideEffectNodeDetector $sideEffectNodeDetector, VariableAnalyzer $variableAnalyzer, BetterNodeFinder $betterNodeFinder, StmtsManipulator $stmtsManipulator, ReflectionProvider $reflectionProvider)
    {
        $this->reservedKeywordAnalyzer = $reservedKeywordAnalyzer;
        $this->sideEffectNodeDetector = $sideEffectNodeDetector;
        $this->variableAnalyzer = $variableAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->stmtsManipulator = $stmtsManipulator;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove unused assigns to variables', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $value = 5;
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
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Function_::class];
    }
    /**
     * @param ClassMethod|Function_ $node
     * @return null|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_
     */
    public function refactor(Node $node)
    {
        $stmts = $node->stmts;
        if ($stmts === null || $stmts === []) {
            return null;
        }
        // we cannot be sure here
        if ($this->shouldSkip($stmts)) {
            return null;
        }
        $assignedVariableNamesByStmtPosition = $this->resolvedAssignedVariablesByStmtPosition($stmts);
        $hasChanged = \false;
        foreach ($assignedVariableNamesByStmtPosition as $stmtPosition => $variableName) {
            if ($this->stmtsManipulator->isVariableUsedInNextStmt($node, $stmtPosition + 1, $variableName)) {
                continue;
            }
            /** @var Expression<Assign> $currentStmt */
            $currentStmt = $stmts[$stmtPosition];
            /** @var Assign $assign */
            $assign = $currentStmt->expr;
            if ($this->isObjectWithDestructMethod($assign->expr)) {
                continue;
            }
            if ($this->hasCallLikeInAssignExpr($assign)) {
                // clean safely
                $cleanAssignedExpr = $this->cleanCastedExpr($assign->expr);
                $newExpression = new Expression($cleanAssignedExpr);
                $this->mirrorComments($newExpression, $currentStmt);
                $node->stmts[$stmtPosition] = $newExpression;
            } else {
                unset($node->stmts[$stmtPosition]);
            }
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function isObjectWithDestructMethod(Expr $expr): bool
    {
        $exprType = $this->getType($expr);
        if (!$exprType instanceof ObjectType) {
            return \false;
        }
        $classReflection = $exprType->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        return $classReflection->hasNativeMethod(MethodName::DESTRUCT);
    }
    private function cleanCastedExpr(Expr $expr): Expr
    {
        if (!$expr instanceof Cast) {
            return $expr;
        }
        return $this->cleanCastedExpr($expr->expr);
    }
    private function hasCallLikeInAssignExpr(Expr $expr): bool
    {
        return (bool) $this->betterNodeFinder->findFirst($expr, \Closure::fromCallable([$this->sideEffectNodeDetector, 'detectCallExpr']));
    }
    /**
     * @param Stmt[] $stmts
     */
    private function shouldSkip(array $stmts): bool
    {
        return (bool) $this->betterNodeFinder->findFirst($stmts, function (Node $node): bool {
            if ($node instanceof Include_) {
                return \true;
            }
            if (!$node instanceof FuncCall) {
                return \false;
            }
            return $this->isNames($node, ['compact', 'get_defined_vars']);
        });
    }
    /**
     * @param string[] $refVariableNames
     */
    private function collectAssignRefVariableNames(Stmt $stmt, array &$refVariableNames): void
    {
        if (!NodeGroup::isStmtAwareNode($stmt)) {
            return;
        }
        $this->traverseNodesWithCallable($stmt, function (Node $subNode) use (&$refVariableNames): Node {
            if ($subNode instanceof AssignRef && $subNode->var instanceof Variable) {
                $refVariableNames[] = (string) $this->getName($subNode->var);
            }
            return $subNode;
        });
    }
    /**
     * @param array<int, Stmt> $stmts
     * @return array<int, string>
     */
    private function resolvedAssignedVariablesByStmtPosition(array $stmts): array
    {
        $assignedVariableNamesByStmtPosition = [];
        $refVariableNames = [];
        foreach ($stmts as $key => $stmt) {
            if (!$stmt instanceof Expression) {
                $this->collectAssignRefVariableNames($stmt, $refVariableNames);
                continue;
            }
            if ($stmt->expr instanceof AssignRef && $stmt->expr->var instanceof Variable) {
                $refVariableNames[] = (string) $this->getName($stmt->expr->var);
            }
            if (!$stmt->expr instanceof Assign) {
                continue;
            }
            $this->traverseNodesWithCallable($stmt->expr->expr, function (Node $subNode) use (&$refVariableNames) {
                if ($subNode instanceof Class_ || $subNode instanceof Function_) {
                    return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
                }
                if (!$subNode instanceof Closure) {
                    return null;
                }
                foreach ($subNode->uses as $closureUse) {
                    if (!$closureUse->var instanceof Variable) {
                        continue;
                    }
                    if (!$closureUse->byRef) {
                        continue;
                    }
                    $refVariableNames[] = (string) $this->getName($closureUse->var);
                }
            });
            $assign = $stmt->expr;
            if (!$assign->var instanceof Variable) {
                continue;
            }
            $variableName = $this->getName($assign->var);
            if (!is_string($variableName)) {
                continue;
            }
            if ($this->reservedKeywordAnalyzer->isNativeVariable($variableName)) {
                continue;
            }
            if ($this->shouldSkipVariable($assign->var, $variableName, $refVariableNames)) {
                continue;
            }
            if ($this->isNoDiscardCall($assign->expr)) {
                continue;
            }
            $assignedVariableNamesByStmtPosition[$key] = $variableName;
        }
        return $assignedVariableNamesByStmtPosition;
    }
    /**
     * @param string[] $refVariableNames
     */
    private function shouldSkipVariable(Variable $variable, string $variableName, array $refVariableNames): bool
    {
        if ($this->variableAnalyzer->isStaticOrGlobal($variable)) {
            return \true;
        }
        if ($this->variableAnalyzer->isUsedByReference($variable)) {
            return \true;
        }
        return in_array($variableName, $refVariableNames, \true);
    }
    private function isNoDiscardCall(Expr $expr): bool
    {
        if ($expr instanceof FuncCall) {
            $name = $this->getName($expr);
            if ($name === null) {
                return \false;
            }
            $scope = ScopeFetcher::fetch($expr);
            if (!$this->reflectionProvider->hasFunction(new Name($name), $scope)) {
                return \false;
            }
            return $this->hasNoDiscardAttribute($this->reflectionProvider->getFunction(new Name($name), $scope)->getAttributes());
        }
        if ($expr instanceof StaticCall) {
            $classNames = $this->getType($expr->class)->getObjectClassNames();
            $methodName = $this->getName($expr->name);
        } elseif ($expr instanceof MethodCall || $expr instanceof NullsafeMethodCall) {
            $classNames = $this->getType($expr->var)->getObjectClassNames();
            $methodName = $this->getName($expr->name);
        } else {
            return \false;
        }
        if ($classNames === [] || $methodName === null) {
            return \false;
        }
        foreach ($classNames as $className) {
            if (!$this->reflectionProvider->hasClass($className)) {
                continue;
            }
            $classReflection = $this->reflectionProvider->getClass($className);
            if (!$classReflection->hasNativeMethod($methodName)) {
                continue;
            }
            if ($this->hasNoDiscardAttribute($classReflection->getNativeMethod($methodName)->getAttributes())) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param AttributeReflection[] $attributes
     */
    private function hasNoDiscardAttribute(array $attributes): bool
    {
        foreach ($attributes as $attribute) {
            if ($attribute->getName() === 'NoDiscard') {
                return \true;
            }
        }
        return \false;
    }
}
