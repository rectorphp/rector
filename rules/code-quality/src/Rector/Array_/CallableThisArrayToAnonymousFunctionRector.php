<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Array_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://www.php.net/manual/en/language.types.callable.php#117260
 * @see https://3v4l.org/MsMbQ
 * @see https://3v4l.org/KM1Ji
 *
 * @see \Rector\CodeQuality\Tests\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\CallableThisArrayToAnonymousFunctionRectorTest
 */
final class CallableThisArrayToAnonymousFunctionRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Convert [$this, "method"] to proper anonymous function', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $values = [1, 5, 3];
        usort($values, [$this, 'compareSize']);

        return $values;
    }

    private function compareSize($first, $second)
    {
        return $first <=> $second;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $values = [1, 5, 3];
        usort($values, function ($first, $second) {
            return $this->compareSize($first, $second);
        });

        return $values;
    }

    private function compareSize($first, $second)
    {
        return $first <=> $second;
    }
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
        return [Array_::class];
    }

    /**
     * @param Array_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkipArray($node)) {
            return null;
        }

        $objectVariable = $node->items[0]->value;
        if (! $objectVariable instanceof Variable && ! $objectVariable instanceof PropertyFetch) {
            return null;
        }

        $methodName = $node->items[1]->value;
        if (! $methodName instanceof String_) {
            return null;
        }

        $classMethod = $this->matchCallableMethod($objectVariable, $methodName);
        if ($classMethod === null) {
            return null;
        }

        return $this->createAnonymousFunction($classMethod, $objectVariable);
    }

    private function shouldSkipArray(Array_ $array): bool
    {
        // callback is exactly "[$two, 'items']"
        if (count($array->items) !== 2) {
            return true;
        }

        // can be totally empty in case of "[, $value]"
        if ($array->items[0] === null) {
            return true;
        }

        if ($array->items[1] === null) {
            return true;
        }

        return $this->isCallbackAtFunctionName($array, 'register_shutdown_function');
    }

    /**
     * @param Variable|PropertyFetch $objectExpr
     */
    private function matchCallableMethod(Expr $objectExpr, String_ $string): ?ClassMethod
    {
        $methodName = $this->getValue($string);
        if (! is_string($methodName)) {
            throw new ShouldNotHappenException();
        }

        $objectType = $this->getObjectType($objectExpr);
        $objectType = $this->popFirstObjectType($objectType);

        if ($objectType instanceof ObjectType) {
            $class = $this->nodeRepository->findClass($objectType->getClassName());

            if ($class === null) {
                return null;
            }

            $classMethod = $class->getMethod($methodName);

            if ($classMethod === null) {
                return null;
            }

            if ($this->isName($objectExpr, 'this')) {
                return $classMethod;
            }

            // is public method of another service
            if ($classMethod->isPublic()) {
                return $classMethod;
            }
        }

        return null;
    }

    /**
     * @param Variable|PropertyFetch $node
     */
    private function createAnonymousFunction(ClassMethod $classMethod, Node $node): Closure
    {
        $classMethodReturns = $this->betterNodeFinder->findInstanceOf((array) $classMethod->stmts, Return_::class);

        $anonymousFunction = new Closure();
        $newParams = $this->copyParams($classMethod->params);

        $anonymousFunction->params = $newParams;

        $innerMethodCall = new MethodCall($node, $classMethod->name);
        $innerMethodCall->args = $this->convertParamsToArgs($newParams);

        if ($classMethod->returnType !== null) {
            $newReturnType = $classMethod->returnType;
            $newReturnType->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            $anonymousFunction->returnType = $newReturnType;
        }

        // does method return something?
        if ($this->hasClassMethodReturn($classMethodReturns)) {
            $anonymousFunction->stmts[] = new Return_($innerMethodCall);
        } else {
            $anonymousFunction->stmts[] = new Expression($innerMethodCall);
        }

        if ($node instanceof Variable && ! $this->isName($node, 'this')) {
            $anonymousFunction->uses[] = new ClosureUse($node);
        }

        return $anonymousFunction;
    }

    private function isCallbackAtFunctionName(Array_ $array, string $functionName): bool
    {
        $parentNode = $array->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof Arg) {
            return false;
        }

        $parentParentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentParentNode instanceof FuncCall) {
            return false;
        }

        return $this->isName($parentParentNode, $functionName);
    }

    private function popFirstObjectType(Type $type): Type
    {
        if ($type instanceof UnionType) {
            foreach ($type->getTypes() as $unionedType) {
                if (! $unionedType instanceof ObjectType) {
                    continue;
                }

                return $unionedType;
            }
        }

        return $type;
    }

    /**
     * @param Param[] $params
     * @return Param[]
     */
    private function copyParams(array $params): array
    {
        $newParams = [];
        foreach ($params as $param) {
            $newParam = clone $param;
            $newParam->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            $newParam->var->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            $newParams[] = $newParam;
        }

        return $newParams;
    }

    /**
     * @param Param[] $params
     * @return Arg[]
     */
    private function convertParamsToArgs(array $params): array
    {
        $args = [];
        foreach ($params as $param) {
            $args[] = new Arg($param->var);
        }

        return $args;
    }

    /**
     * @param Return_[] $nodes
     */
    private function hasClassMethodReturn(array $nodes): bool
    {
        foreach ($nodes as $node) {
            if ($node->expr !== null) {
                return true;
            }
        }
        return false;
    }
}
