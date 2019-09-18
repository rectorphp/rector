<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Array_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ObjectType;
use Rector\NodeContainer\ParsedNodesByType;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://www.php.net/manual/en/language.types.callable.php#117260
 * @see https://3v4l.org/MsMbQ
 * @see https://3v4l.org/KM1Ji
 *
 * @see \Rector\CodeQuality\Tests\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\CallableThisArrayToAnonymousFunctionRectorTest
 */
final class CallableThisArrayToAnonymousFunctionRector extends AbstractRector
{
    /**
     * @var ParsedNodesByType
     */
    private $parsedNodesByType;

    public function __construct(ParsedNodesByType $parsedNodesByType)
    {
        $this->parsedNodesByType = $parsedNodesByType;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Convert [$this, "method"] to proper anonymous function', [
            new CodeSample(
                <<<'PHP'
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
PHP
                ,
                <<<'PHP'
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
PHP
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

    /**
     * @param Param[] $params
     * @return Arg[]
     */
    private function convertParamsToArgs(array $params): array
    {
        $args = [];
        foreach ($params as $key => $param) {
            $args[$key] = new Arg($param->var);
        }

        return $args;
    }

    /**
     * @param Variable|PropertyFetch $objectExpr
     */
    private function matchCallableMethod(Expr $objectExpr, String_ $methodExpr): ?ClassMethod
    {
        $methodName = $this->getValue($methodExpr);
        $objectType = $this->getObjectType($objectExpr);

        if ($objectType instanceof ObjectType) {
            $class = $this->parsedNodesByType->findClass($objectType->getClassName());

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

    private function shouldSkipArray(Array_ $array): bool
    {
        // callback is exactly "[$two, 'items']"
        if (count($array->items) !== 2) {
            return true;
        }

        // can be totally empty in case of "[, $value]"
        return $array->items[0] === null;
    }

    /**
     * @param Variable|PropertyFetch $node
     */
    private function createAnonymousFunction(ClassMethod $classMethod, Node $node): Closure
    {
        $hasClassMethodReturn = $this->betterNodeFinder->findInstanceOf((array) $classMethod->stmts, Return_::class);

        $anonymousFunction = new Closure();
        $anonymousFunction->params = $classMethod->params;

        $innerMethodCall = new MethodCall($node, $classMethod->name);
        $innerMethodCall->args = $this->convertParamsToArgs($classMethod->params);

        if ($classMethod->returnType !== null) {
            $anonymousFunction->returnType = $classMethod->returnType;
        }

        // does method return something?
        if ($hasClassMethodReturn) {
            $anonymousFunction->stmts[] = new Return_($innerMethodCall);
        } else {
            $anonymousFunction->stmts[] = new Expression($innerMethodCall);
        }

        if ($node instanceof Variable) {
            if (! $this->isName($node, 'this')) {
                $anonymousFunction->uses[] = new ClosureUse($node);
            }
        }

        return $anonymousFunction;
    }
}
