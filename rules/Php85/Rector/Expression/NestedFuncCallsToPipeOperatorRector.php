<?php

declare (strict_types=1);
namespace Rector\Php85\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Pipe;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\VariadicPlaceholder;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php85\Rector\Expression\NestedFuncCallsToPipeOperatorRector\NestedFuncCallsToPipeOperatorRectorTest
 */
final class NestedFuncCallsToPipeOperatorRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Convert multiple nested function calls in single line to |> pipe operator', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($input)
    {
        $result = trim(strtolower(htmlspecialchars($input)));
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($input)
    {
        $result = $input
            |> htmlspecialchars(...)
            |> strtolower(...)
            |> trim(...);
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [Expression::class, Return_::class];
    }
    /**
     * @param Expression|Return_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $hasChanged = \false;
        if ($node->expr instanceof Assign) {
            $assign = $node->expr;
            $assignedValue = $assign->expr;
            $processedValue = $this->processNestedCalls($assignedValue);
            if ($processedValue instanceof Expr && $processedValue !== $assignedValue) {
                $assign->expr = $processedValue;
                $hasChanged = \true;
            }
        } elseif ($node->expr instanceof FuncCall) {
            $funcCall = $node->expr;
            $processedValue = $this->processNestedCalls($funcCall);
            if ($processedValue instanceof Expr && $processedValue !== $funcCall) {
                $node->expr = $processedValue;
                $hasChanged = \true;
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::PIPE_OPERATOER;
    }
    private function buildPipeExpression(FuncCall $funcCall, Expr $expr): Pipe
    {
        // Recursively process inner call to unwrap all nested levels
        if ($expr instanceof FuncCall) {
            $processed = $this->processNestedCalls($expr, \true);
            $nestedInner = $processed instanceof Expr ? $processed : $expr;
        } else {
            $nestedInner = $expr;
        }
        return new Pipe($nestedInner, $this->createPlaceholderCall($funcCall));
    }
    private function createPlaceholderCall(FuncCall $funcCall): FuncCall
    {
        return new FuncCall($funcCall->name, [new VariadicPlaceholder()]);
    }
    private function processNestedCalls(Expr $expr, bool $deep = \false): ?Expr
    {
        if (!$expr instanceof FuncCall) {
            return null;
        }
        if ($expr->isFirstClassCallable()) {
            return null;
        }
        if (count($expr->args) !== 1) {
            return null;
        }
        // Check if any argument is a function call
        foreach ($expr->args as $arg) {
            if (!$arg instanceof Arg) {
                return null;
            }
            if ($arg->value instanceof FuncCall) {
                return $this->buildPipeExpression($expr, $arg->value);
            }
            // If we're deep in recursion and hit a non-FuncCall, this is the base
            if ($deep) {
                // Return a pipe with the base expression on the left
                return new Pipe($arg->value, $this->createPlaceholderCall($expr));
            }
        }
        return null;
    }
}
