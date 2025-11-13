<?php

declare (strict_types=1);
namespace Rector\Unambiguous\Rector\Expression;

use PHPStan\Type\ObjectType;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Reflection\ClassReflection;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
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
    public function __construct(PropertyNaming $propertyNaming)
    {
        $this->propertyNaming = $propertyNaming;
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
        $firstMethodCall = $node->expr;
        // must be nested method call, so we avoid only single one
        if (!$firstMethodCall->var instanceof MethodCall) {
            return null;
        }
        /** @var MethodCall[] $methodCalls */
        $methodCalls = [];
        $currentMethodCall = $firstMethodCall;
        while ($currentMethodCall instanceof MethodCall) {
            $methodCalls[] = $currentMethodCall;
            $currentMethodCall = $currentMethodCall->var;
        }
        // at least 2 method calls
        if (count($methodCalls) < 1) {
            return null;
        }
        $rootExpr = $currentMethodCall;
        if ($this->shouldSkipForVendorOrInternal($firstMethodCall)) {
            return null;
        }
        $variableName = $this->resolveVariableName($rootExpr);
        $someVariable = new Variable($variableName);
        $firstAssign = new Assign($someVariable, $rootExpr);
        $stmts = [new Expression($firstAssign)];
        foreach ($methodCalls as $methodCall) {
            $methodCall->var = $someVariable;
            // inlines indent and removes () around first expr
            $methodCall->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            $stmts[] = new Expression($methodCall);
        }
        $node->expr = $someVariable;
        return $stmts;
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
}
