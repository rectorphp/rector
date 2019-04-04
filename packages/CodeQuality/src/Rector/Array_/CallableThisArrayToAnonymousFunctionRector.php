<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Array_;

use PhpParser\Node;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://3v4l.org/MsMbQ
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

        $thisVariable = $node->items[0]->value;
        if (! $this->isThisVariable($thisVariable)) {
            return null;
        }

        $classMethod = $this->matchLocalMethod($node->items[1]->value);
        if ($classMethod === null) {
            return null;
        }

        $anonymousFunction = new Node\Expr\Closure();
        $anonymousFunction->params = $classMethod->params;

        $innerMethodCall = new Node\Expr\MethodCall($thisVariable, $classMethod->name);
        $innerMethodCall->args = $this->convertParamsToArgs($classMethod->params);

        if ($classMethod->returnType) {
            $anonymousFunction->returnType = $classMethod->returnType;
        }

        $anonymousFunction->stmts[] = new Node\Stmt\Return_($innerMethodCall);

        return $anonymousFunction;
    }

    private function isThisVariable(Node\Expr $expr): bool
    {
        if (! $expr instanceof Node\Expr\Variable) {
            return true;
        }

        return $this->isName($expr, 'this');
    }

    private function matchLocalMethod(Node\Expr $expr): ?Node\Stmt\ClassMethod
    {
        if (! $expr instanceof Node\Scalar\String_) {
            return null;
        }

        $possibleMethodName = $expr->value;

        /** @var Node\Stmt\Class_|null $classNode */
        $classNode = $expr->getAttribute(Attribute::CLASS_NODE);
        if ($classNode === null) {
            return null;
        }

        return $classNode->getMethod($possibleMethodName);
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
}
