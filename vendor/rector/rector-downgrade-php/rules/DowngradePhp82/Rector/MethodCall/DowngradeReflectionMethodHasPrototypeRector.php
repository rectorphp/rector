<?php

declare (strict_types=1);
namespace Rector\DowngradePhp82\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\TryCatch;
use PHPStan\Type\ObjectType;
use Rector\Naming\Naming\VariableNaming;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp82\Rector\MethodCall\DowngradeReflectionMethodHasPrototypeRector\DowngradeReflectionMethodHasPrototypeRectorTest
 */
final class DowngradeReflectionMethodHasPrototypeRector extends AbstractRector
{
    /**
     * @readonly
     */
    private VariableNaming $variableNaming;
    public function __construct(VariableNaming $variableNaming)
    {
        $this->variableNaming = $variableNaming;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Downgrade ReflectionMethod::hasPrototype() by emulating it with getPrototype()', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(ReflectionMethod $reflectionMethod): bool
    {
        return $reflectionMethod->hasPrototype();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(ReflectionMethod $reflectionMethod): bool
    {
        return (function (\ReflectionMethod $reflectionMethod2): bool {
            try {
                $reflectionMethod2->getPrototype();
                return true;
            } catch (\ReflectionException) {
                return false;
            }
        })($reflectionMethod);
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
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!$this->isName($node->name, 'hasPrototype')) {
            return null;
        }
        if (!$this->isObjectType($node->var, new ObjectType('ReflectionMethod'))) {
            return null;
        }
        $scope = ScopeFetcher::fetch($node);
        $parameterName = $this->variableNaming->createCountedValueName('reflectionMethod', $scope);
        return new FuncCall($this->createClosure($parameterName), [new Arg($node->var)]);
    }
    private function createClosure(string $parameterName): Closure
    {
        $reflectionMethodVariable = new Variable($parameterName);
        $tryCatch = new TryCatch([new Expression(new MethodCall($reflectionMethodVariable, 'getPrototype')), new Return_(new ConstFetch(new Name('true')))], [new Catch_([new FullyQualified('ReflectionException')], null, [new Return_(new ConstFetch(new Name('false')))])]);
        return new Closure(['params' => [new Param($reflectionMethodVariable, null, new FullyQualified('ReflectionMethod'))], 'returnType' => new Identifier('bool'), 'stmts' => [$tryCatch]]);
    }
}
