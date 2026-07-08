<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Class_;

use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Class_\InnerFunctionToPrivateMethodRector\InnerFunctionToPrivateMethodRectorTest
 */
final class InnerFunctionToPrivateMethodRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Turn an inner named function declared inside a class method into a private method, as inner named functions are not supported by PHPStan', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(): void
    {
        function inner(): void
        {
            echo 'hello';
        }

        inner();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(): void
    {
        $this->inner();
    }

    private function inner(): void
    {
        echo 'hello';
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
    public function refactor(Node $node): ?Node
    {
        $existingMethodNames = array_map(fn(ClassMethod $classMethod): string => $classMethod->name->toString(), $node->getMethods());
        $newClassMethods = [];
        foreach ($node->getMethods() as $classMethod) {
            $innerFunctions = $this->extractInnerFunctions($classMethod, $existingMethodNames);
            if ($innerFunctions === []) {
                continue;
            }
            $isStatic = $classMethod->isStatic();
            $innerFunctionNames = array_map(fn(Function_ $innerFunction): string => $innerFunction->name->toString(), $innerFunctions);
            foreach ($innerFunctions as $innerFunction) {
                $newClassMethod = $this->createPrivateMethod($innerFunction, $isStatic);
                $this->rewriteCallsToMethod($newClassMethod, $innerFunctionNames, $isStatic);
                $newClassMethods[] = $newClassMethod;
            }
            $this->rewriteCallsToMethod($classMethod, $innerFunctionNames, $isStatic);
        }
        if ($newClassMethods === []) {
            return null;
        }
        $node->stmts = array_merge($node->stmts, $newClassMethods);
        return $node;
    }
    /**
     * @param string[] $existingMethodNames
     * @return Function_[]
     */
    private function extractInnerFunctions(ClassMethod $classMethod, array &$existingMethodNames): array
    {
        $innerFunctions = [];
        foreach ($classMethod->stmts ?? [] as $key => $stmt) {
            if (!$stmt instanceof Function_) {
                continue;
            }
            $functionName = $stmt->name->toString();
            // avoid collision with an already existing method
            if (in_array($functionName, $existingMethodNames, \true)) {
                continue;
            }
            // avoid breaking string callables, e.g. usort($items, 'functionName')
            if ($this->isReferencedAsStringCallable($classMethod, $functionName)) {
                continue;
            }
            $existingMethodNames[] = $functionName;
            $innerFunctions[] = $stmt;
            unset($classMethod->stmts[$key]);
        }
        if ($innerFunctions !== []) {
            $classMethod->stmts = array_values((array) $classMethod->stmts);
        }
        return $innerFunctions;
    }
    private function isReferencedAsStringCallable(ClassMethod $classMethod, string $functionName): bool
    {
        $isReferenced = \false;
        $this->traverseNodesWithCallable($classMethod->stmts ?? [], function (Node $node) use ($functionName, &$isReferenced) {
            if ($node instanceof String_ && $node->value === $functionName) {
                $isReferenced = \true;
            }
            return null;
        });
        return $isReferenced;
    }
    private function createPrivateMethod(Function_ $innerFunction, bool $isStatic): ClassMethod
    {
        $flags = Modifiers::PRIVATE;
        if ($isStatic) {
            $flags |= Modifiers::STATIC;
        }
        $classMethod = new ClassMethod($innerFunction->name, ['flags' => $flags, 'byRef' => $innerFunction->byRef, 'params' => $innerFunction->params, 'returnType' => $innerFunction->returnType, 'stmts' => $innerFunction->stmts, 'attrGroups' => $innerFunction->attrGroups]);
        $this->mirrorComments($classMethod, $innerFunction);
        return $classMethod;
    }
    /**
     * @param string[] $innerFunctionNames
     */
    private function rewriteCallsToMethod(ClassMethod $classMethod, array $innerFunctionNames, bool $isStatic): void
    {
        $this->traverseNodesWithCallable($classMethod->stmts ?? [], function (Node $node) use ($innerFunctionNames, $isStatic) {
            if (!$node instanceof FuncCall) {
                return null;
            }
            if (!$node->name instanceof Name) {
                return null;
            }
            $functionName = $node->name->toString();
            if (!in_array($functionName, $innerFunctionNames, \true)) {
                return null;
            }
            if ($isStatic) {
                return new StaticCall(new Name('self'), $functionName, $node->args);
            }
            return new MethodCall(new Variable('this'), $functionName, $node->args);
        });
    }
}
