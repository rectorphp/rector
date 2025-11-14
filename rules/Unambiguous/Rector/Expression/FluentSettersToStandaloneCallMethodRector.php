<?php

declare (strict_types=1);
namespace Rector\Unambiguous\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\Unambiguous\NodeAnalyzer\FluentMethodCallsCollector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @experimental since 2025-11
 *
 * @see \Rector\Tests\Unambiguous\Rector\Expression\FluentSettersToStandaloneCallMethodRector\FluentSettersToStandaloneCallMethodRectorTest
 */
final class FluentSettersToStandaloneCallMethodRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PropertyNaming $propertyNaming;
    /**
     * @readonly
     */
    private FluentMethodCallsCollector $fluentMethodCallsCollector;
    public function __construct(PropertyNaming $propertyNaming, FluentMethodCallsCollector $fluentMethodCallsCollector)
    {
        $this->propertyNaming = $propertyNaming;
        $this->fluentMethodCallsCollector = $fluentMethodCallsCollector;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change fluent setter chain calls, to standalone line of setters', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return (new SomeFluentClass())
            ->setName('John')
            ->setAge(30);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $someFluentClass = new SomeFluentClass();
        $someFluentClass->setName('John');
        $someFluentClass->setAge(30);

        return $someFluentClass;
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
        return [Expression::class, Return_::class];
    }
    /**
     * @param Expression|Return_ $node
     */
    public function refactor(Node $node): ?array
    {
        if (!$node->expr instanceof MethodCall) {
            return null;
        }
        $methodCalls = $this->fluentMethodCallsCollector->resolve($node->expr);
        // at least 2 method calls
        if (count($methodCalls) < 1) {
            return null;
        }
        $lastMethodCall = end($methodCalls);
        $rootExpr = $lastMethodCall->var;
        if (!$rootExpr instanceof New_) {
            return null;
        }
        if ($this->shouldSkipForVendorOrInternal($node->expr)) {
            return null;
        }
        $variableName = $this->resolveVariableName($rootExpr);
        $someVariable = new Variable($variableName);
        $firstAssign = new Assign($someVariable, $rootExpr);
        return $this->createStmts($firstAssign, $methodCalls, $someVariable, $node);
    }
    private function resolveVariableName(Expr $expr): string
    {
        if (!$expr instanceof New_) {
            return 'someVariable';
        }
        if ($expr->class instanceof Name) {
            return $this->propertyNaming->fqnToVariableName($expr->class);
        }
        return 'someVariable';
    }
    private function shouldSkipForVendorOrInternal(MethodCall $firstMethodCall): bool
    {
        $callerType = $this->getType($firstMethodCall);
        if ($callerType instanceof ObjectType) {
            $classReflection = $callerType->getClassReflection();
            if (!$classReflection instanceof ClassReflection) {
                return \false;
            }
            $fileName = $classReflection->getFileName();
            if ($fileName === null || strpos($fileName, 'vendor') !== \false) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param MethodCall[] $methodCalls
     * @return Stmt[]
     * @param \PhpParser\Node\Stmt\Expression|\PhpParser\Node\Stmt\Return_ $firstStmt
     */
    private function createStmts(Assign $firstAssign, array $methodCalls, Variable $someVariable, $firstStmt): array
    {
        $stmts = [new Expression($firstAssign)];
        // revert to normal order
        $methodCalls = array_reverse($methodCalls);
        foreach ($methodCalls as $methodCall) {
            $methodCall->var = $someVariable;
            // inlines indent and removes () around first expr
            $methodCall->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            $stmts[] = new Expression($methodCall);
        }
        if ($firstStmt instanceof Return_) {
            $firstStmt->expr = $someVariable;
            $stmts[] = $firstStmt;
        }
        $firstStmt->expr = $someVariable;
        return $stmts;
    }
}
