<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Array_;

use PhpParser\Node;
use Rector\NodeTypeResolver\Application\ClassLikeNodeCollector;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://3v4l.org/MsMbQ
 * @see https://3v4l.org/KM1Ji
 */
final class CallableThisArrayToAnonymousFunctionRector extends AbstractRector
{
    /**
     * @var ClassLikeNodeCollector
     */
    private $classLikeNodeCollector;

    public function __construct(ClassLikeNodeCollector $classLikeNodeCollector)
    {
        $this->classLikeNodeCollector = $classLikeNodeCollector;
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
        return [Node\Expr\Array_::class];
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

        $anonymousFunction = new Node\Expr\Closure();
        $anonymousFunction->params = $classMethod->params;

        $innerMethodCall = new Node\Expr\MethodCall($objectVariable, $classMethod->name);
        $innerMethodCall->args = $this->convertParamsToArgs($classMethod->params);

        if ($classMethod->returnType) {
            $anonymousFunction->returnType = $classMethod->returnType;
        }

        $anonymousFunction->stmts[] = new Node\Stmt\Return_($innerMethodCall);

        if ($objectVariable instanceof Node\Expr\Variable) {
            if (! $this->isName($objectVariable, 'this')) {
                $anonymousFunction->uses[] = new Node\Expr\ClosureUse($objectVariable);
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
            $args[$key] = new Node\Arg($param->var);
        }

        return $args;
    }

    private function matchCallableMethod(Node\Expr $objectExpr, Node\Expr $methodExpr): ?Node\Stmt\ClassMethod
    {
        $methodName = $this->getValue($methodExpr);

        foreach ($this->getTypes($objectExpr) as $type) {
            $class = $this->classLikeNodeCollector->findClass($type);
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
