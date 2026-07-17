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
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202607\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Php85\Rector\Expression\NestedFuncCallsToPipeOperatorRector\NestedFuncCallsToPipeOperatorRectorTest
 */
final class NestedFuncCallsToPipeOperatorRector extends AbstractRector implements MinPhpVersionInterface, ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const MINIMUM_DEPTH = 'minimum_depth';
    /**
     * @var int
     */
    private const DEFAULT_MINIMUM_DEPTH = 2;
    private int $minimumDepth = self::DEFAULT_MINIMUM_DEPTH;
    /**
     * @param array<string, mixed> $configuration
     */
    public function configure(array $configuration): void
    {
        $minimumDepth = $configuration[self::MINIMUM_DEPTH] ?? self::DEFAULT_MINIMUM_DEPTH;
        Assert::integer($minimumDepth);
        Assert::greaterThanEq($minimumDepth, 2);
        $this->minimumDepth = $minimumDepth;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Convert multiple nested function calls in single line to |> pipe operator', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
, [self::MINIMUM_DEPTH => 3])]);
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
        if (!$deep && $this->countNestedFuncCalls($expr) < $this->minimumDepth) {
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
            // Spread argument can't be converted to pipe — keep the call as-is
            if ($arg->unpack) {
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
    private function countNestedFuncCalls(FuncCall $funcCall): int
    {
        $depth = 1;
        while (count($funcCall->args) === 1) {
            $arg = $funcCall->args[0];
            if (!$arg instanceof Arg || $arg->unpack || !$arg->value instanceof FuncCall) {
                break;
            }
            ++$depth;
            $funcCall = $arg->value;
        }
        return $depth;
    }
}
