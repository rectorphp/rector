<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Array_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeContainer\ParsedNodesByType;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://www.php.net/manual/en/language.types.callable.php#117260
 * @see https://3v4l.org/MsMbQ
 * @see https://3v4l.org/KM1Ji
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
     * @param Node\Expr\Array_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (count($node->items) !== 2) {
            return null;
        }

        // is callable
        $objectVariable = $node->items[0]->value;

        $classMethod = $this->matchCallableMethod($objectVariable, $node->items[1]->value);
        if ($classMethod === null) {
            return null;
        }

        $anonymousFunction = new Closure();
        $anonymousFunction->params = $classMethod->params;

        $innerMethodCall = new MethodCall($objectVariable, $classMethod->name);
        $innerMethodCall->args = $this->convertParamsToArgs($classMethod->params);

        if ($classMethod->returnType !== null) {
            $anonymousFunction->returnType = $classMethod->returnType;
        }

        $anonymousFunction->stmts[] = new Return_($innerMethodCall);

        if ($objectVariable instanceof Variable) {
            if (! $this->isName($objectVariable, 'this')) {
                $anonymousFunction->uses[] = new ClosureUse($objectVariable);
            }
        }

        return $anonymousFunction;
    }

    /**
     * @param Node\Param[] $params
     * @return Node\Arg[]
     */
    private function convertParamsToArgs(array $params): array
    {
        $args = [];
        foreach ($params as $key => $param) {
            $args[$key] = new Arg($param->var);
        }

        return $args;
    }

    private function matchCallableMethod(Expr $objectExpr, Expr $methodExpr): ?ClassMethod
    {
        $methodName = $this->getValue($methodExpr);

        foreach ($this->getTypes($objectExpr) as $type) {
            $class = $this->parsedNodesByType->findClass($type);
            if ($class === null) {
                continue;
            }

            $classMethod = $class->getMethod($methodName);
            if ($classMethod === null) {
                continue;
            }

            if ($this->isName($objectExpr, 'this')) {
                return $classMethod;
            }

            if ($classMethod->isPublic()) {
                return $classMethod;
            }

            return null;
        }

        return null;
    }
}
